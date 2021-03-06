<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class portfoliosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $portfolios = Portfolio::orderBy("updated_at","DESC")->get();
        return view("management_panel.portfolios.index",compact("portfolios"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('management_panel.portfolios.create')->render();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "title_image"=>"required",
            "title"=>"required",
            "content"=>"required",
            "descriptions" =>"required"
        ]);

        $portfolio = new Portfolio();
        $portfolio->title = $request->title;
        $portfolio->slug = Str::slug($request->title);
        $portfolio->content = $request->content;
        $portfolio->descriptions = $request->descriptions;

        if($request->hasFile('title_image')){
            $newImageName = Str::uuid().".".$request->file("title_image")->extension();
            $request->file("title_image")->move(public_path("img/portfolios"),$newImageName);
            $portfolio->title_image = $newImageName;
        }
        $portfolio->save();

        if($request->hasFile('images')){
            foreach ($request->file("images") as $image){
                $gallery = new Gallery();
                $newImageName = Str::uuid().".".$image->extension();
                $image->move(public_path("img/portfolios"),$newImageName);
                $gallery->url = $newImageName;
                $gallery->portfolios_id = $portfolio->id;
                $gallery->save();
            }
        }

        return redirect("/admin/portfolios")->with("success","Portfolyo ba??ar??l?? bir ??ekilde kay??t edilmi??tir.");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Portfolio  $portfolio
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $portfolio = Portfolio::where("id",$id)->firstOrFail();

        return view("management_panel.portfolios.edit",compact("portfolio"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Portfolio  $portfolio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Portfolio $portfolio, $id)
    {
        $request->validate([
            "title"=>"required",
            "content"=>"required",
            "descriptions"=>"required"
        ]);

        $portfolio = Portfolio::findOrFail($id);
        $portfolio->title = $request->title;
        $portfolio->slug = Str::slug($request->title);
        $portfolio->content = $request->content;
        $portfolio->descriptions = $request->descriptions;

        if($request->hasFile('title_image')){
            unlink(public_path("img/portfolios/".$portfolio->title_image));
            $newImageName = Str::uuid().".".$request->file("title_image")->extension();
            $request->file("title_image")->move(public_path("img/portfolios"),$newImageName);
            $portfolio->title_image = $newImageName;
        }
        $portfolio->save();

        if($request->hasFile('images')){

            foreach($portfolio->getGallery as $image){
                unlink(public_path("img/portfolios/".$image->url));
                $image->delete();
            }

            foreach ($request->file("images") as $image){
                $gallery = new Gallery();
                $newImageName = Str::uuid().".".$image->extension();
                $image->move(public_path("img/portfolios"),$newImageName);
                $gallery->url = $newImageName;
                $gallery->portfolios_id = $portfolio->id;
                $gallery->save();
            }
        }

        return redirect("/admin/portfolios")->with("success","Portfolyo ba??ar??l?? bir ??ekilde g??ncellenmi??tir.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Portfolio  $portfolio
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $request->validate(["id"=>"required"]);
        $portfolio = Portfolio::findOrFail($request->id);

        unlink(public_path("img/portfolios/".$portfolio->title_image));
        foreach($portfolio->getGallery as $image){
            unlink(public_path("img/portfolios/".$image->url));
            $image->delete();
        }

        $portfolio->delete();
        return redirect()->back()->with("success","Portfolyo ba??ar??l?? bir ??ekilde silinmi??tir.");
    }
}

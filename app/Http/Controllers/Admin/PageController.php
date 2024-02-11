<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function index(Request $request){
        $pages = Page::latest();
        if ($request->get('keyword') != "") {
            $pages = $pages->where('name','like','%'.$request->keyword.'%');
        }
        $pages = $pages->paginate(10);
        return view('admin.pages.index',compact('pages'));
    }


    public function create(){
        return view('admin.pages.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => ['required','max:190'],
            'slug' => ['required','max:190'],
            'content' => ['required','max:1900'],
        ]);

        if ($validator->passes()) {

            $page = new Page();
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();

            session()->flash('success','Page Create Successfuly.');
            return response()->json([
                'status' => true,
                'message' => 'Page Create Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function edit(Request $request, $id){
        $page = Page::find($id);
        return view('admin.pages.edit',compact('page'));
    }


    public function update(Request $request,$id){
        $page = Page::find($id);

        $validator = Validator::make($request->all(),[
            'name' => ['required','max:190'],
            'slug' => ['required','max:190'],
            'content' => ['required','max:1900'],
        ]);

        if ($validator->passes()) {

            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();

            session()->flash('success','Page Updated Successfuly.');
            return response()->json([
                'status' => true,
                'message' => 'Page Updated Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request, $id){
        $page = Page::find($id);

        session()->flash('error','Recod not Found');
        if ($page == null) {
            return response()->json([
                'status' => false,
                'message' => 'Recod not Found'
            ]);
        }

        $page->delete();
        session()->flash('success', 'Page Delete Successfully');
        return response()->json([
            'status' => true,
            'message' => 'Page Delete Successfully.'
        ]);
    }

}

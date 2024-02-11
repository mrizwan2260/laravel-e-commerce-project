<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Image;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest();
        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $categories = $categories->paginate(10);
        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50'],
            'slug' => ['required', 'max:50', 'unique:categories'],
        ]);
        if ($validator->passes()) {
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            //save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '.' . $ext;
                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                //Generate Image Thumbnail
                $dPath = public_path() . '/uploads/category/thumbnail/' . $newImageName;
                $img = Image::make($sPath);
                // $img->resize(450, 600);
                // add callback functionality to retain maximal original image size
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success', 'Category Added SuccessFully.');

            return response()->json([
                'status' => true,
                'message' => 'Category Added SuccessFully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            return to_route('category.index');
        }
        return view('admin.category.edit', compact('category'));
    }

    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not Found',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50'],
            'slug' => ['required', 'max:50', 'unique:categories,slug,' . $category->id . ',id'],
        ]);
        if ($validator->passes()) {

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            $oldImage = $category->image;

            //save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '-' . time() . '.' . $ext;
                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                //Generate Image Thumbnail
                $dPath = public_path() . '/uploads/category/thumbnail/' . $newImageName;
                $img = Image::make($sPath);
                // $img->resize(450, 600);
                // add callback functionality to retain maximal original image size
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();

                //old image delete here
                File::delete(public_path('/uploads/category/thumbnail/' . $oldImage));
                File::delete(public_path('/uploads/category/' . $oldImage));
            }

            $request->session()->flash('success', 'Category Updated SuccessFully.');

            return response()->json([
                'status' => true,
                'message' => 'Category Updated SuccessFully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function destroy($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        $request->session()->flash('error','Category not found');
        if(empty($category)){
            // return to_route('category.index');
            return response()->json([
                'status' => true,
                'message' => 'category not Found',
            ]);
        }
        //image delete here
        File::delete(public_path('/uploads/category/thumbnail/' . $category->image));
        File::delete(public_path('/uploads/category/' . $category->image));
        $category->delete();

        $request->session()->flash('success','Category Delete SuccessFully.');

        return response()->json([
            'status' => true,
            'message' => 'category delete successfully',
        ]);
    }
}

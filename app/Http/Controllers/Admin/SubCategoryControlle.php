<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryControlle extends Controller
{
    public function index(Request $request)
    {
        $subCategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')->latest('sub_categories.id')
            ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $subCategories = $subCategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
            $subCategories = $subCategories->orWhere('categories.name', 'like', '%' . $request->get('keyword') . '%');
        }
        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.index', compact('subCategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('admin.sub_category.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:191'],
            'slug' => ['required', 'max:191', 'unique:sub_categories'],
            'category' => ['required'],
            'status' => ['required'],
        ]);

        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success', 'Sub Category Added SuccessFully.');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category Added SuccessFully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function edit($id, Request $request)
    {
        $subCategories = SubCategory::find($id);
        $request->session()->flash('error', 'Record Not Found');
        if (empty($subCategories)) {
            return to_route('sub-categories.index');
        }
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('admin.sub_category.edit', compact('categories', 'subCategories'));
    }

    public function update($id, Request $request)
    {
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            $request->session()->flash('error', 'Record Not Found');
            return response([
                'status' => false,
                'notfound' => true
            ]);
            // return to_route('sub-categories.index');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:191'],
            'slug' => ['required', 'max:191', 'unique:sub_categories,slug,' . $subCategory->id . ',id'],
            'category' => ['required'],
            'status' => ['required'],
        ]);

        if ($validator->passes()) {
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success', 'Sub Category Updated SuccessFully.');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category Updated SuccessFully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy($id, Request $request)
    {
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            $request->session()->flash('error', 'Record Not Found');
            return response([
                'status' => false,
                'notfound' => true
            ]);
        }

        $subCategory->delete();
        $request->session()->flash('success', 'Sub Category Deleted SuccessFully.');
        return response([
            'status' => true,
            'message' => 'Sub Category Deleted SuccessFully.'
        ]);
    }
}

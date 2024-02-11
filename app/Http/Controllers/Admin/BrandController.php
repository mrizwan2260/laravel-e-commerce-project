<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = brand::latest('id');
        if ($request->get('keyword')) {
            $brands = $brands->where('name', 'like', '%' . $request->keyword . '%');
        }
        $brands = $brands->paginate(10);
        return view('admin.brand.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brand.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:191'],
            'slug' => ['required', 'unique:brands'],
            'status' => ['required'],
        ]);

        if ($validator->passes()) {
            $brand = new brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand Created SuccessFully.');
            return response()->json([
                'status' => true,
                'message' => 'Brand Created SuccessFully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        $brand = brand::find($id);

        if (empty($brand)) {
            $request->session()->flash('error', 'Record not Found');
            return to_route('brand.index');
        }
        return view('admin.brand.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = brand::find($id);

        if (empty($brand)) {
            $request->session()->flash('error', 'Record not Found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:191'],
            'slug' => ['required', 'unique:brands,slug, ' . $brand->id . ',id'],
            'status' => ['required'],
        ]);

        if ($validator->passes()) {
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand Updated SuccessFully.');
            return response()->json([
                'status' => true,
                'success' => 'Brand Updated SuccessFully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($brandId, Request $request)
    {
        $brand = brand::find($brandId);
        if (empty($brand)) {
            $request->session()->flash('error', 'Record not Found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $brand->delete();
        $request->session()->flash('success', 'Brand Deleted SuccessFully.');
        return response()->json([
            'status' => true,
            'success' => 'Brand Deleted SuccessFully.'
        ]);
    }
}

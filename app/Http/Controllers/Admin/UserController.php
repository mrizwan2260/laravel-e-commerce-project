<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request){
        $users = User::latest();
        if(!empty($request->get('keyword'))){
            $users = $users->where('name','like','%'.$request->get('keyword').'%');
            $users = $users->orWhere('email','like','%'.$request->get('keyword').'%');
        }

        $users = $users->paginate(10);

        return view('admin.users.index',compact('users'));
    }


    public function create(Request $request){
        return view('admin.users.create');
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            'email' => ['required','unique:users'],
            'password' => ['required','min:5'],
            'phone' => ['required',],
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->role = $request->role;
            $user->save();

            session()->flash('success','User Added Successfully.');
            return response()->json([
                'status' => true,
                'message' => 'User Added Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit(Request $request, $id){
        $user = User::find($id);
        if ($user == null) {
            return to_route('user.index')->with('error', 'Record not Found.');
        }
        return view('admin.users.edit',compact('user'));
    }

    public function update(Request $request, $id){

        $user = User::find($id);
        if ($user == null) {
            return to_route('user.index')->with('error', 'Record not Found.');
        }

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            // 'email' => ['required','unique:users'],
            // 'password' => ['required','min:5'],
            'phone' => ['required',],
        ]);

        if ($validator->passes()) {
            $user->name = $request->name;
            // $user->email = $request->email;
            // if ($user != '') {
            //     $user->password = Hash::make($request->password);
            // }
            $user->status = $request->status;
            $user->role = $request->role;
            $user->phone = $request->phone;
            $user->save();

            session()->flash('success','User Update Successfully.');
            return response()->json([
                'status' => true,
                'message' => 'User Update Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if ($user == null) {
            session()->flash('error','Record not Found.');
            return response()->json([
            'status' => true,
            'message' => 'Record not Found.'
        ]);
        }

        $user->delete();

        session()->flash('success','User Delete Successfully.');
        return response()->json([
            'status' => true,
            'message' => 'User Delete Successfully.'
        ]);
    }

}


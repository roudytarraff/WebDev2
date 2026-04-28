<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(){
        return view('auth.login');
    }

    public function connect(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            if(Auth::user()->role=='Admin'){
                //change this
                //return redirect()->route('doctors.index');
            }else{
                //change this
                //return redirect()->route('doctors.index');
            }
        }

        return back()->withErrors(['email'=>"Wrong email or password"]);
    }


    public function register(){
        return view('auth.register');
    }

    public function create(Request $request){

        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|confirmed'
        ]);

        $row = new User();
        $row->name = $request->name;
        $row->email = $request->email;

        $row->role='User';

        $row->password=Hash::make($request->password);
        $row->save();
        Auth::login($row);
        //change this
        //return redirect()->route('doctors.index');

    }

    public function logout(){
        Auth::logout();
        return redirect()->route('auth.login');
    }
}

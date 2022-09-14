<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;

class UserController extends Controller
{
//    this function for sent the password reset pin to registered email
    public function recovery(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered Email again'
                ]);
            }
        }
            $validated = $validator->validated();
            $user = User::where('email', '=', $validated['email'])->first();
            if ($user == null) {
                return response([
                    'status' => 400,
                    'message' => 'Your Email is not registered...!'
                ]);
            }

        try {
            $pin = Random::generate(4, '0-9');
//            session()->put('reset_email', $validated['email']);
//            session()->put('reset_pin', $pin);
//            session()->put('reset_pin_status', 'sent');
            $user->reset_pin = $pin;
            $user->save();
            return response([
                'status' => 200,
                'message' => 'use this pin to reset your password',
                'pin' => $pin,
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => 'Something went wrong please try again later...!',
                'error' => $e->getMessage(),
            ]);
        }
    }

//    this function for validated the password reset pin
    public function checkResetPin(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'pin' => 'required|max:4|min:4',

        ]);
        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered Email again'
                ]);
            } elseif ($validator->errors()->has('pin')) {
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered pin code again'
                ]);
            }
        }
            $validated = $validator->validated();
            $user = User::where('email', '=', $validated['email'])->first();
            if ($user == null) {
                return response([
                    'status' => 400,
                    'message' => 'Your Email is not registered...!'
                ]);
            }

        try {

            if (!isset($user->reset_pin)){
                return response([
                    'status' => 400,
                    'message' => 'Please request to password reset'
                ]);
            }
            if ($user->reset_pin != $validated['pin']){
                return response([
                    'status' => 400,
                    'message' => 'You entered pin is invalid'
                ]);
            }
//            session()->forget('reset_pin');
//            session()->put('reset_pin_status', 'success');
            $user->reset_pin = 'valid';
            $user->save();
            return response([
                'status' => 200,
                'message' => 'Your password reset pin validated Now you can reset password',
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => 'Something went wrong please try again later...!',
                'error' => $e->getMessage(),
            ]);
        }
    }

//    this functions for reset the password
    public function resetPassword(Request $request)
    {

        $validator = validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|max:32|min:8',

        ]);
        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered Email again'
                ]);
            } elseif ($validator->errors()->has('password')) {
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered password again'
                ]);
            }
        }
            $validated = $validator->validated();
            $user = User::where('email', '=', $validated['email'])->first();
            if ($user == null) {
                return response([
                    'status' => 400,
                    'message' => 'Your Email is not registered...!'
                ]);
            }

        try {
            if (!isset($user->reset_pin)){
                return response([
                    'status' => 400,
                    'message' => 'Please request to password reset'
                ]);
            }
            if ($user->reset_pin != 'valid'){
                return response([
                    'status' => 400,
                    'message' => 'You validate your password reset pin, before reset password'
                ]);
            }
            $user->password = Hash::make( $validated['password']);
            $user->reset_pin = null;
            $user->save();
//            session()->forget('reset_pin_status');
//            session()->forget('reset_pin');
//            session()->forget('reset_mail');
//            session()->flush();
            return response([
                'status' => 200,
                'message' => 'Your password reset successfully...! Try to login now',
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => 'Something went wrong please try again later...!'
            ]);
        }
    }

//    this function for insert logged user data
    public function register(Request $request)
    {
        $validator = validator::make($request->all(),[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|max:32|min:8',
        ]);
        if ($validator->fails()){
            if($validator->errors()->has('name')){
                return response([
                    'status' => 400,
                    'message' => 'Please Check your name again'
                ]);
            }elseif($validator->errors()->has('email')){
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered Email again'
                ]);
            }elseif($validator->errors()->has('password')){
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered password again'
                ]);
            }
        }
        $validated = $validator->validated();
        $exists = User::where('email', '=', $validated['email'])->first();
        if ($exists != null){
            return response([
                'status' => 400,
                'message' => 'Your Email is already registered...! Try to login now'
            ]);
        }
        try{
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make( $validated['password']);
            $user->save();
            return response([
                'status' => 200,
                'message' => 'Dear '.$validated['name'].', Thank you join with us...! your account created successfully... Now You can login using you entered email and password'
            ]);
               }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong please try again later...!'
            ]);
        }


    }

//    this function for logged valid users to the system
    public function login(Request $request)
    {
        $validator =  Validator::make($request->all(),[
            'email' => 'required|email|max:255',
            'password' => 'required|max:32|min:8',
        ]);
        if ($validator->fails()){
            if($validator->errors()->has('email')){
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered Email again'
                ]);
            }elseif($validator->errors()->has('password')){
                return response([
                    'status' => 400,
                    'message' => 'Please Check you entered password again'
                ]);
            }
        }
        $validated = $validator->validated();
        try{
            $user = User::where('email','=', $validated['email'])->first();
            if($user != null){
                if( Hash::check($validated['password'], $user->password)){
//                    if ($request->remember == 'on'){
//                        setcookie('email', $validated['email'], time() + (86400 * 30), "/");
//                        setcookie('password', $validated['password'], time() + (86400 * 30), "/");
//                    }
                    $token = $user->createToken('calendar-token')->plainTextToken;
                    session()->put('auth_user', $user);
                    return response([
                        'status' => 200,
                        'message' => 'Logged successfully',
                        'user' => $user,
                        'token' => $token
                    ]);
                }else{
                    return response([
                        'status' => 400,
                        'message' => 'Dear '. $validated['email'] .', you entered password is wrong',
                    ]);
                }
            }else{
                return response([
                    'status' => 400,
                    'message' => 'You entered email address not registered',
                ]);
            }
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }
    }
}

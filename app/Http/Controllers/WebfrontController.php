<?php

namespace employment_bank\Http\Controllers;

use Illuminate\Http\Request;

use employment_bank\Http\Requests;
use employment_bank\Http\Controllers\Controller;
use employment_bank\Models\Candidate;
use employment_bank\Models\PostedJob;
use Kris\LaravelFormBuilder\FormBuilder;
use Illuminate\Support\Str;
use employment_bank\Helpers\Basehelper;
use Validator, Redirect, DB;
class WebfrontController extends Controller{

    private $content  = 'webfront.';
    //private $route    = 'master.boards.';

    public function getHome(){

        $postedjobs = PostedJob::with('industry')->paginate(20);
        return view('webfront.index', compact('postedjobs'));
    }

    public function showRegister(){

        return view('webfront.candidate.register');
    }

    public function doRegister(Request $request){

        $validator = Validator::make($data = $request->all(), Candidate::$rules, Candidate::$messages);
        if ($validator->fails())
          return Redirect::back()->withErrors($validator)->withInput();

        if ($validator->passes()){

          //$confirmation_code = Str::quickRandom(30);
          $confirmation_code = '12345';
      		$candidate = new Candidate;
      		//$candidate->name = ucwords($request->fullname);
        	$candidate->username = $request->username;
        	$candidate->mobile_no = $request->mobile_no;
        	$candidate->email = $request->email;
        	$candidate->password = bcrypt($request->password);
        	$candidate->confirmation_code = $confirmation_code;

        	$data = ['confirmation_code' => $confirmation_code,
        			 'username' => $request->username,
        			 'password' => $request->password,
        			 'mobile_no' => $request->mobile_no
        	];

      		Basehelper::sendSMS($request->mobile_no, 'Hello '.$request->username.', you have successfully registere. Your username is '.$request->username.' and password is '.$request->password);

    	  	// Mail::send('emails.verify', $data, function($message) use ($candidate, $data){
    	  	// 	$message->from('no-reply@employment_bank', 'Employment Bank');
          //     	$message->to(Input::get('email'), $candidate->name)
          //         	->subject('Verify your email address');
          // });

        	if(!$candidate->save())
  	  		return Redirect::back()->with('message', 'Error while creating your account!<br> Please contact Technical Support');

  	  	  return Redirect::route('candidate.login')->with('message', 'Account has been created!<br>Now Check your email address to verify your account by checking your spam folder or inboxes for verification link after that you can login');
      	  	//sendConfirmation() Will go the email and sms as needed

        }else{

      		return Redirect::back()->withInput()
                	->withErrors($validation);
        }

    }

}

<?php

namespace App\Http\Controllers;
//  you need to add this manual in visual studio code this is not automatic
use App\Http\Requests\UserUpdate;
//  you need to add this manual in visual studio code this is not automatic
use App\Comment;
use Illuminate\Http\Request;
// you need to add this manual in visual studio code this is not automatic
use Illuminate\Support\Facades\Auth;
// you need to add this manual in visual studio code this is not automatic
use Hash;

//// you need to add this manual in visual studio code this is not automatic



use App\Charts\DashboardChart;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    //

    public function __construct()
    {
         $this->middleware('auth');

    }
    public function dashboard(){

        $chart = new DashboardChart;
        $days = $this->generateDateRange(Carbon::now()->subDays(30),Carbon::now());
        $comments  = [];

        foreach ($days as $day){
            $comments[] = Comment::whereDate('created_at',$day)->where('user_id',Auth::id())->count();
        }

        
        $chart->dataset('Comments','line',$comments);
        $chart->labels($days);
        return view('user.dashboards',compact('chart'));
    }

    private function generateDateRange(Carbon $start_date , Carbon $end_date){

        $dates = [];
        for($date = $start_date; $date->lte($end_date); $date->addDay()){
            $dates[] = $date->format('Y-m-d');

        }
        return $dates;
    }

    public function comments(){
        return view('user.comments');
    }


    public function profile(){
        return view('user.profile');
    }


    public function ProfilePost(UserUpdate $request){
        
        $user = Auth::user();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->save();
        if($request['password'] !=""){
            if(!(Hash::check($request['password'],Auth::user()->password))){
                return redirect()->back()->with('error','Your current password does not match with the password you provided');
            }

            if(strcmp($request['password'],$request['new_password']) == 0){
                return redirect()->back()->with('error','New password cannot be the same ');

            }

            $validation = $request->validate([
                'password' => 'required',
                'new_password' => 'required|string|min:6|confirmed'
            ]);

             $user->password = bcrypt($request['new_password']);
             $user->save();

             return redirect()->back()->with('success','Passowrd changed Successfully' );
        }

       return back();
    }

    public function deleComment($id){

        $comment = Comment::where('id',$id)->where('user_id',Auth::id())->delete();
       
        return back();
    }



    public function newComment(Request $request){


        $comment = new Comment;
        $comment->post_id = request('post');

        $comment->user_id = Auth::id();
        $comment->content = request('comment');
        $comment->save();

        return back();

    }
}

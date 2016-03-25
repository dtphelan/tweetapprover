<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Abraham\TwitterOAuth\TwitterOAuth;

class TweetController extends Controller {

    /* Index of approved tweets, responds to /tweet */
    public function getIndex() {
        $tweets = \App\Tweet::where('status', 'LIKE', 1)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.index')
            ->with('tweets',$tweets);
    }

    /* Marks a tweet as used */
    public function postUsed(Request $request) {
        $tweet = \App\Tweet::where('id', 'LIKE', $request->id)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->first();
        $tweet->status = $request->status;

        $tweet->save();

        return redirect('/tweet');
    }

    /* Form to create a new tweet */
    public function getCreate() {
        return view('tweet.create');
    }

    /* Responds to POST Create, adds tweet to database, returns create form with confirmation */
    public function postCreate(Request $request) {
        $this->validate(
            $request,
            [
                'tweet' => 'required|max:140',
            ]
        );

        $tweet = new \App\Tweet();
        $tweet->tweet = $request->tweet;
        $tweet->status = $request->status;
        $tweet->author = $request->author;
        $tweet->organization = $request->organization;
        $tweet->countDisplay = $request->countDisplay;
        
        $tweet->save();

        $confirm = 'yes';

        return view('tweet.create')
            ->with('confirm',$confirm);
    }

    public function postBitly(Request $request) {
        $this->validate(
            $request,
            [
                'longUrl' => 'required',
            ]
        );

        $longUrl = $request->longUrl;

        $my_bitly = new \Hpatoio\Bitly\Client("d28d2149f4f3417f7c6ef56d860415580e736f74");

        $response = $my_bitly->Shorten(array('longUrl' => $longUrl));
        $url = $response['url'];

        return view('tweet.create')
            ->with('url',$url);
    }


    /* Responds to /approve, gets tweets that need approval */
    public function getApprove() {
        $tweet = \App\Tweet::where('status', 'LIKE', 0)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.approve')
            ->with('tweet',$tweet);
    }

    /* Responds to POST /approve, approves tweet */
    public function postApprove(Request $request) {
        $this->validate(
            $request,
            [
                'tweet' => 'required|max:140',
            ]
        );

        $tweet = \App\Tweet::where('id', 'LIKE', $request->id)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->first();
        $tweet->tweet = $request->tweet;
        $tweet->status = $request->status;
        $tweet->comment = $request->comment;

        $tweet->save();

        $tweet = \App\Tweet::where('status', 'LIKE', 0)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.approve')
            ->with('tweet',$tweet);

        }

    /* Responds to /tweet/revise, makes a list of tweets that need revision */
    public function getRevise() {
        $tweets = \App\Tweet::where('status', 'LIKE', 5)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.revise')
            ->with('tweets',$tweets);
    }

    /* Revises and resubmits tweet */
    public function postRevise(Request $request) {
        $tweet = \App\Tweet::where('id', 'LIKE', $request->id)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->first();
        $tweet->status = $request->status;
        $tweet->tweet = $request->tweet;

        $tweet->save();

        $confirm = 'yes';

        $tweets = \App\Tweet::where('status', 'LIKE', 5)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.revise')
            ->with('confirm',$confirm)
            ->with('tweets',$tweets);
    }

    /* Responds to /tweet/used, makes a list of used tweets */
    public function getUsed() {
        $tweets = \App\Tweet::where('status', 'LIKE', 3)
            ->orwhere('status', 'LIKE', 4)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->get();

        return view('tweet.used')
            ->with('tweets',$tweets);
    }


    /* Deletes a tweet from the database */
    public function postDelete(Request $request) {
        $tweet = \App\Tweet::where('id', 'LIKE', $request->id)
            ->where('organization', 'LIKE', Auth::user()->organization)
            ->first();

        $tweet->delete();

        return redirect('/tweet/used');
    }

} # eoc

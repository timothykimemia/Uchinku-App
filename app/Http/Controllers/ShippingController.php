<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Shop;
use App\User;
use App\Seller;
use App\Shipping;
use App\Shippingagent;
use App\BusinessSetting;
use Auth;
use Hash;

class ShippingController extends Controller
{

    public function __construct()
    {
        $this->middleware('user', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shipping = Auth::user()->shipping;
        return view('frontend.shipping_agents.shipping', compact('shipping'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::check() && Auth::user()->user_type == 'admin'){
            flash(__('Admin can not be a shipping agents'))->error();
            return back();
        }
        else{
            return view('frontend.shippingagent_form');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = null;
        if(!Auth::check()){
            if(User::where('email', $request->email)->first() != null){
                flash(__('Email already exists!'))->error();
                return back();
            }
            if($request->password == $request->password_confirmation){
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->user_type = "shippingagent";
                $user->password = Hash::make($request->password);
                $user->save();
            }
            else{
                flash(__('Sorry! Password did not match.'))->error();
                return back();
            }
        }
        else{
            $user = Auth::user();
            if($user->customer != null){
                $user->customer->delete();
            }
            $user->user_type = "shippingagent";
            $user->save();
        }

        if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
            $user->email_verified_at = date('Y-m-d H:m:s');
            $user->save();
        }

        $shippingagents = new Shippingagent();
        $shippingagents->user_id = $user->id;
        $shippingagents->save();

        if(Shipping::where('user_id', $user->id)->first() == null){
            $shipping = new Shipping;
            $shipping->user_id = $user->id;
            $shipping->name = $request->name;
            $shipping->address = $request->address;
            $shipping->slug = preg_replace('/\s+/', '-', $request->name).'-'.$shipping->id;

            if($shipping->save()){
                auth()->login($user, false);
                flash(__('Your Shipping agent account has been created successfully!'))->success();
                return redirect()->route('shipping_agents.index');
            }
            else{
                $shipping->delete();
                $user->user_type == 'customer';
                $user->save();
            }
        }

        flash(__('Sorry! Something went wrong.'))->error();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);

        if($request->has('name') && $request->has('address')){
            $shipping->name = $request->name;
            if ($request->has('shipping_cost')) {
                $shipping->shipping_cost = $request->shipping_cost;
            }
            $shipping->address = $request->address;
            $shipping->slug = preg_replace('/\s+/', '-', $request->name).'-'.$shipping->id;

            $shipping->meta_title = $request->meta_title;
            $shipping->meta_description = $request->meta_description;

            if($request->hasFile('logo')){
                $shipping->logo = $request->logo->store('uploads/shop/logo');
            }

            if ($request->has('pick_up_point_id')) {
                $shipping->pick_up_point_id = json_encode($request->pick_up_point_id);
            }
            else {
                $shipping->pick_up_point_id = json_encode(array());
            }
        }

        elseif($request->has('facebook') || $request->has('google') || $request->has('twitter') || $request->has('youtube') || $request->has('instagram')){
            $shipping->facebook = $request->facebook;
            $shipping->google = $request->google;
            $shipping->twitter = $request->twitter;
            $shipping->youtube = $request->youtube;
        }

        else{
            if($request->has('previous_sliders')){
                $sliders = $request->previous_sliders;
            }
            else{
                $sliders = array();
            }

            if($request->hasFile('sliders')){
                foreach ($request->sliders as $key => $slider) {
                    array_push($sliders, $slider->store('uploads/shop/sliders'));
                }
            }

            $shipping->sliders = json_encode($sliders);
        }

        if($shipping->save()){
            flash(__('Your Shipping account  has been updated successfully!'))->success();
            return back();
        }

        flash(__('Sorry! Something went wrong.'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function verify_form(Request $request)
    {
        if(Auth::user()->shippingagent->verification_info == null){
            $shop = Auth::user()->shop;
            return view('frontend.shipping_agents.verify_form', compact('shop'));
        }
        else {
            flash(__('Sorry! You have sent verification request already.'))->error();
            return back();
        }
    }

    public function verify_form_store(Request $request)
    {
        $data = array();
        $i = 0;
        foreach (json_decode(BusinessSetting::where('type', 'verification_form')->first()->value) as $key => $element) {
            $item = array();
            if ($element->type == 'text') {
                $item['type'] = 'text';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i];
            }
            elseif ($element->type == 'select' || $element->type == 'radio') {
                $item['type'] = 'select';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i];
            }
            elseif ($element->type == 'multi_select') {
                $item['type'] = 'multi_select';
                $item['label'] = $element->label;
                $item['value'] = json_encode($request['element_'.$i]);
            }
            elseif ($element->type == 'file') {
                $item['type'] = 'file';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i]->store('uploads/verification_form');
            }
            array_push($data, $item);
            $i++;
        }
        $shippingagent = Auth::user()->shippingagent;
        $shippingagent->verification_info = json_encode($data);
        if($shippingagent->save()){
            flash(__('Your shipping agent verification request has been submitted successfully!'))->success();
            return redirect()->route('dashboard');
        }

        flash(__('Sorry! Something went wrong.'))->error();
        return back();
    }
}

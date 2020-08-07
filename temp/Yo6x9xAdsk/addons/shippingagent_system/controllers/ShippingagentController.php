<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShippingagentOption;
use App\Addon;
use App\Order;
use App\BusinessSetting;
use App\ShippingagentConfig;
use App\ShippingagentUser;
use App\ShippingagentPayment;
use App\ShippingagentEarningDetail;
use App\User;
use App\Customer;
use App\Category;
use Session;
use Cookie;
use Auth;
use Hash;

class ShippingagentController extends Controller
{
    //
    public function index(){
        return view('shippingagent.index');
    }

    public function shippingagent_option_store(Request $request){
        //dd($request->all());
        $shippingagent_option = ShippingagentOption::where('type', $request->type)->first();
        if($shippingagent_option == null){
            $shippingagent_option = new ShippingagentOption;
        }
        $shippingagent_option->type = $request->type;

        $commision_details = array();
        if ($request->type == 'user_registration_first_purchase') {
            $shippingagent_option->percentage = $request->percentage;
        }
        elseif ($request->type == 'product_sharing') {
            $commision_details['commission'] = $request->amount;
            $commision_details['commission_type'] = $request->amount_type;
        }
        elseif ($request->type == 'category_wise_shippingagent') {
            foreach(Category::all() as $category) {
                $data['category_id'] = $request['categories_id_'.$category->id];
                $data['commission'] = $request['commison_amounts_'.$category->id];
                $data['commission_type'] = $request['commison_types_'.$category->id];
                array_push($commision_details, $data);
            }
        }
        elseif ($request->type == 'max_shippingagent_limit') {
            $shippingagent_option->percentage = $request->percentage;
        }
        $shippingagent_option->details = json_encode($commision_details);
        if ($request->has('status')) {
            $shippingagent_option->status = 1;
        }
        else {
            $shippingagent_option->status = 0;
        }
        $shippingagent_option->save();
        flash("This has been updated successfully")->success();
        return back();
    }

    public function configs(){
            return view('shippingagent.configs');
    }

    public function config_store(Request $request){
        $form = array();
        $select_types = ['select', 'multi_select', 'radio'];
        $j = 0;
        for ($i=0; $i < count($request->type); $i++) {
            $item['type'] = $request->type[$i];
            $item['label'] = $request->label[$i];
            if(in_array($request->type[$i], $select_types)){
                $item['options'] = json_encode($request['options_'.$request->option[$j]]);
                $j++;
            }
            array_push($form, $item);
        }
        $shippingagent_config = ShippingagentConfig::where('type', 'verification_form')->first();
        $shippingagent_config->value = json_encode($form);
        if($shippingagent_config->save()){
            flash("Verification form updated successfully")->success();
            return back();
        }
    }

    public function apply_for_shippingagent(Request $request){
        return view('shippingagent.frontend.apply_for_shippingagent');
    }

    public function store_shippingagent_user(Request $request){
        if(!Auth::check()){
            if(User::where('email', $request->email)->first() != null){
                flash(__('Email already exists!'))->error();
                return back();
            }
            if($request->password == $request->password_confirmation){
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->user_type = "customer";
                $user->password = Hash::make($request->password);
                $user->save();

                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();

                auth()->login($user, false);
            }
            else{
                flash(__('Sorry! Password did not match.'))->error();
                return back();
            }
        }

        $shippingagent_user = Auth::user()->shippingagent_user;
        if ($shippingagent_user == null) {
            $shippingagent_user = new ShippingagentUser;
            $shippingagent_user->user_id = Auth::user()->id;
        }
        $data = array();
        $i = 0;
        foreach (json_decode(ShippingagentConfig::where('type', 'verification_form')->first()->value) as $key => $element) {
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
                $item['value'] = $request['element_'.$i]->store('uploads/shippingagent_verification_form');
            }
            array_push($data, $item);
            $i++;
        }
        $shippingagent_user->informations = json_encode($data);
        if($shippingagent_user->save()){
            flash(__('Your verification request has been submitted successfully!'))->success();
            return redirect()->route('home');
        }

        flash(__('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function users(){
        $shippingagent_users = ShippingagentUser::paginate(12);
        return view('shippingagent.users', compact('shippingagent_users'));
    }

    public function show_verification_request($id){
        $shippingagent_user = ShippingagentUser::findOrFail($id);
        return view('shippingagent.show_verification_request', compact('shippingagent_user'));
    }

    public function approve_user($id)
    {
        $shippingagent_user = ShippingagentUser::findOrFail($id);
        $shippingagent_user->status = 1;
        if($shippingagent_user->save()){
            flash(__('Shippingagent user has been approved successfully'))->success();
            return redirect()->route('shippingagent.users');
        }
        flash(__('Something went wrong'))->error();
        return back();
    }

    public function reject_user($id)
    {
        $shippingagent_user = ShippingagentUser::findOrFail($id);
        $shippingagent_user->status = 0;
        $shippingagent_user->informations = null;
        if($shippingagent_user->save()){
            flash(__('Shippingagent user request has been rejected successfully'))->success();
            return redirect()->route('shippingagent.users');
        }
        flash(__('Something went wrong'))->error();
        return back();
    }

    public function updateApproved(Request $request)
    {
        $shippingagent_user = ShippingagentUser::findOrFail($request->id);
        $shippingagent_user->status = $request->status;
        if($shippingagent_user->save()){
            return 1;
        }
        return 0;
    }

    public function payment_modal(Request $request)
    {
        $shippingagent_user = ShippingagentUser::findOrFail($request->id);
        return view('shippingagent.payment_modal', compact('shippingagent_user'));
    }

    public function payment_store(Request $request){
        $shippingagent_payment = new ShippingagentPayment;
        $shippingagent_payment->shippingagent_user_id = $request->shippingagent_user_id;
        $shippingagent_payment->amount = $request->amount;
        $shippingagent_payment->payment_method = $request->payment_method;
        $shippingagent_payment->save();

        $shippingagent_user = ShippingagentUser::findOrFail($request->shippingagent_user_id);
        $shippingagent_user->balance -= $request->amount;
        $shippingagent_user->save();

        flash(__('Payment completed'))->success();
        return back();
    }

    public function payment_history($id){
        $shippingagent_user = ShippingagentUser::findOrFail(decrypt($id));
        $shippingagent_payments = $shippingagent_user->shippingagent_payments();
        return view('shippingagent.payment_history', compact('shippingagent_payments', 'shippingagent_user'));
    }

    public function user_index(){
        $shippingagent_user = Auth::user()->shippingagent_user;
        $shippingagent_payments = $shippingagent_user->shippingagent_payments();
        return view('shippingagent.frontend.index', compact('shippingagent_payments'));
    }

    public function payment_settings(){
        $shippingagent_user = Auth::user()->shippingagent_user;
        return view('shippingagent.frontend.payment_settings', compact('shippingagent_user'));
    }

    public function payment_settings_store(Request $request){
        $shippingagent_user = Auth::user()->shippingagent_user;
        $shippingagent_user->paypal_email = $request->paypal_email;
        $shippingagent_user->bank_information = $request->bank_information;
        $shippingagent_user->save();
        flash(__('Shippingagent payment settings has been updated successfully'))->success();
        return redirect()->route('shippingagent.user.index');
    }

    public function processShippingagentPoints(Order $order){
        if(Addon::where('unique_identifier', 'shippingagent_system')->first() != null && \App\Addon::where('unique_identifier', 'shippingagent_system')->first()->activated){
            if(ShippingagentOption::where('type', 'user_registration_first_purchase')->first()->status){
                if ($order->user != null && $order->user->orders->count() == 1) {
                    if($order->user->referred_by != null){
                        $user = User::find($order->user->referred_by);
                        if ($user != null) {
                            $amount = (ShippingagentOption::where('type', 'user_registration_first_purchase')->first()->percentage * $order->grand_total)/100;
                            $shippingagent_user = $user->shippingagent_user;
                            if($shippingagent_user != null){
                                $shippingagent_user->balance += $amount;
                                $shippingagent_user->save();
                            }
                        }
                    }
                }
            }
            if(ShippingagentOption::where('type', 'product_sharing')->first()->status){
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $amount = 0;
                    if($orderDetail->product_referral_code != null){
                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();
                        if($referred_by_user != null) {
                            if(ShippingagentOption::where('type', 'product_sharing')->first()->details != null && json_decode(ShippingagentOption::where('type', 'product_sharing')->first()->details)->commission_type == 'amount'){
                                $amount = json_decode(ShippingagentOption::where('type', 'product_sharing')->first()->details)->commission;
                            }
                            elseif(ShippingagentOption::where('type', 'product_sharing')->first()->details != null && json_decode(ShippingagentOption::where('type', 'product_sharing')->first()->details)->commission_type == 'percent') {
                                $amount = (json_decode(ShippingagentOption::where('type', 'product_sharing')->first()->details)->commission * $orderDetail->price)/100;
                            }
                            $shippingagent_user = $referred_by_user->shippingagent_user;
                            if($shippingagent_user != null){
                                $shippingagent_user->balance += $amount;
                                $shippingagent_user->save();
                            }
                        }
                    }
                }
            }
            elseif (ShippingagentOption::where('type', 'category_wise_shippingagent')->first()->status) {
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $amount = 0;
                    if($orderDetail->product_referral_code != null) {
                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();
                        if($referred_by_user != null) {
                            if(ShippingagentOption::where('type', 'category_wise_shippingagent')->first()->details != null){
                                foreach (json_decode(ShippingagentOption::where('type', 'category_wise_shippingagent')->first()->details) as $key => $value) {
                                    if($value->category_id == $orderDetail->product->category->id){
                                        if($value->commission_type == 'amount'){
                                            $amount = $value->commission;
                                        }
                                        else {
                                            $amount = ($value->commission * $orderDetail->price)/100;
                                        }
                                    }
                                }
                            }
                            $shippingagent_user = $referred_by_user->shippingagent_user;
                            if($shippingagent_user != null){
                                $shippingagent_user->balance += $amount;
                                $shippingagent_user->save();
                            }
                        }
                    }
                }
            }
        }
    }

    public function refferal_users()
    {
        $refferal_users = User::where('referred_by', '!=' , null)->paginate(10);
        return view('shippingagent.refferal_users', compact('refferal_users'));
    }
}

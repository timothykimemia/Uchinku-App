<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Seller;
use App\User;
use App\Shop;
use App\Product;
use App\Shipping;
use App\Shippingagent;

use App\Order;
use App\OrderDetail;
use Illuminate\Support\Facades\Hash;

class ShippingagentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $approved = null;
        $shippingagent = Shippingagent::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'seller')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $shippingagent = $shippingagent->where(function($shippingagent) use ($user_ids){
                $shippingagent->whereIn('user_id', $user_ids);
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $shippingagent = $shippingagent->where('verification_status', $approved);
        }
        $shipping = $shippingagent->paginate(15);
        return view('shipping_agents.index', compact('shippingagents', 'sort_search', 'approved'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shipping_agents.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(User::where('email', $request->email)->first() != null){
            flash(__('Email already exists!'))->error();
            return back();
        }
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_type = "seller";
        $user->password = Hash::make($request->password);
        if($user->save()){
            $shippingagent = new Shippingagent;
            $shippingagent->user_id = $user->id;
            if($shippingagent->save()){
                $shipping = new Shipping;
                $shipping->user_id = $user->id;
                $shipping->slug = 'demo-shipping-'.$user->id;
                $shipping->save();
                flash(__('Shipping agent has been inserted successfully'))->success();
                return redirect()->route('shipping_agents.index');
            }
        }

        flash(__('Something went wrong'))->error();
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
        $shippingagent = Shippingagent::findOrFail(decrypt($id));
        return view('Shipping_agents.edit', compact('shippingagent'));
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
        $shippingagent = Shippingagent::findOrFail($id);
        $user = $shippingagent->user;
        $user->name = $request->name;
        $user->email = $request->email;
        if(strlen($request->password) > 0){
            $user->password = Hash::make($request->password);
        }
        if($user->save()){
            if($shippingagent->save()){
                flash(__('Shipping agent has been updated successfully'))->success();
                return redirect()->route('shipping_agents.index');
            }
        }

        flash(__('Something went wrong'))->error();
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
        $shippingagent = Shippingagent::findOrFail($id);
        Shipping::where('user_id', $shippingagent->user->id)->delete();
        Product::where('user_id', $shippingagent->user->id)->delete();
        Order::where('user_id', $shippingagent->user->id)->delete();
        OrderDetail::where('seller_id', $shippingagent->user->id)->delete();
        User::destroy($shippingagent->user->id);
        if(Seller::destroy($id)){
            flash(__('Shipping agent has been deleted successfully'))->success();
            return redirect()->route('shipping_agents.index');
        }
        else {
            flash(__('Something went wrong'))->error();
            return back();
        }
    }

    public function show_verification_request($id)
    {
        $shippingagent = Shippingagent::findOrFail($id);
        return view('Shipping_agents.verification', compact('shippingagent'));
    }

    public function approve_shippingagent($id)
    {
        $shippingagent = Shippingagent::findOrFail($id);
        $shippingagent->verification_status = 1;
        if($shippingagent->save()){
            flash(__('Shipping agents has been approved successfully'))->success();
            return redirect()->route('shipping_agents.index');
        }
        flash(__('Something went wrong'))->error();
        return back();
    }

    public function reject_shippingagent($id)
    {
        $shippingagent = Shippingagent::findOrFail($id);
        $shippingagent->verification_status = 0;
        $shippingagent->verification_info = null;
        if($shippingagent->save()){
            flash(__('Shipping agent verification request has been rejected successfully'))->success();
            return redirect()->route('sellers.index');
        }
        flash(__('Something went wrong'))->error();
        return back();
    }


    public function payment_modal(Request $request)
    {
        $shippingagent = Shippingagent::findOrFail($request->id);
        return view('shipping_agents.payment_modal', compact('seller'));
    }

    public function profile_modal(Request $request)
    {
        $shippingagent = Shippingagent::findOrFail($request->id);
        return view('shipping_agents.profile_modal', compact('seller'));
    }

    public function updateApproved(Request $request)
    {
        $shippingagent = Shippingagent::findOrFail($request->id);
        $shippingagent->verification_status = $request->status;
        if($shippingagent->save()){
            return 1;
        }
        return 0;
    }
}

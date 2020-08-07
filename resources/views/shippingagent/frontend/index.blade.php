@extends('frontend.layouts.app')

@section('content')
    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                    @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer')
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12 d-flex align-items-center">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{__('Shipping Agents')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{__('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('shippingagent.user.index') }}">{{__('Shippingagent System')}}</a></li>
                                        </ul>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 offset-md-2">
                                <div class="dashboard-widget text-center green-widget text-white mt-4 c-pointer">
                                    <i class="fa fa-dollar"></i>
                                    <span class="d-block title heading-3 strong-400">{{ single_price(Auth::user()->shippingagent_user->balance) }}</span>
                                    <span class="d-block sub-title">{{ __('Shipping Agent Balance') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('shippingagent.payment_settings') }}">
                                    <div class="dashboard-widget text-center plus-widget mt-4 c-pointer">
                                        <i class="la la-cog"></i>
                                        <span class="d-block title heading-6 strong-400 c-base-1">{{ __('Configure Payout') }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        @if (\App\Addon::where('unique_identifier', 'shippingagent_system')->first() != null && \App\Addon::where('unique_identifier', 'shippingagent_system')->first()->activated && \App\ShippingagentOption::where('type', 'user_registration_first_purchase')->first()->status)
                            <div class="row">
                                @php
                                    if(Auth::user()->referral_code == null){
                                        Auth::user()->referral_code = substr(Auth::user()->id.str_random(10), 0, 10);
                                        Auth::user()->save();
                                    }
                                    $referral_code = Auth::user()->referral_code;
                                    $referral_code_url = URL::to('/users/registration')."?referral_code=$referral_code";
                                @endphp
                                {{-- <div class="col">
                                    <div class="form-box bg-white mt-4">
                                        <div class="form-box-content p-3">
                                            <div class="form-group">
                                                    <textarea id="referral_code_url" class="form-control"
                                                              readonly type="text" >{{$referral_code_url}}</textarea>
                                            </div>
                                            <button type=button id="ref-cpurl-btn" class="btn btn-base-1"
                                                    data-attrcpy="{{__('Copied')}}"
                                                    onclick="copyToClipboard('url')" >{{__('Copy Url')}}</button>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        @endif

                        <div class="card no-border mt-5">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">{{__('Shipping Agent payment history')}}</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-responsive-md mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{__('Amount')}}</th>
                                            <th>{{__('Payment Method')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($shippingagent_payments) > 0)
                                            @foreach ($shippingagent_payments as $key => $shippingagent_payment)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>{{ date('d-m-Y', strtotime($shippingagent_payment->created_at)) }}</td>
                                                    <td>{{ single_price($shippingagent_payment->amount) }}</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $shippingagent_payment ->payment_method)) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center pt-5 h4" colspan="100%">
                                                    <i class="la la-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">{{ __('No history found.') }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="pagination-wrapper py-4">
                            <ul class="pagination justify-content-end">
                                {{ $shippingagent_payments->links() }}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


@section('script')
    <script>
        function copyToClipboard(btn){
            // var el_code = document.getElementById('referral_code');
            var el_url = document.getElementById('referral_code_url');
            // var c_b = document.getElementById('ref-cp-btn');
            var c_u_b = document.getElementById('ref-cpurl-btn');

            // if(btn == 'code'){
            //     if(el_code != null && c_b != null){
            //         el_code.select();
            //         document.execCommand('copy');
            //         c_b .innerHTML  = c_b.dataset.attrcpy;
            //     }
            // }

            if(btn == 'url'){
                if(el_url != null && c_u_b != null){
                    el_url.select();
                    document.execCommand('copy');
                    c_u_b .innerHTML  = c_u_b.dataset.attrcpy;
                }
            }
        }
    </script>
@endsection

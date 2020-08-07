@extends('frontend.layouts.app')

@section('content')

    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-9 mx-auto">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{__('Shippingagent Informations')}}
                                    </h2>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{__('Home')}}</a></li>
                                            <li class="active"><a href="{{ route('shippingagent.apply') }}">{{__('Shippingagent')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form class="" action="{{ route('shippingagent.store_shippingagent_user') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if (!Auth::check())
                                <div class="form-box bg-white mt-4">
                                    <div class="form-box-title px-3 py-2">
                                        {{__('User Info')}}
                                    </div>
                                    <div class="form-box-content p-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <!-- <label>{{ __('Name') }}</label> -->
                                                    <div class="input-group input-group--style-1">
                                                        <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{ __('Name') }}" name="name">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-user"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <!-- <label>{{ __('Email') }}</label> -->
                                                    <div class="input-group input-group--style-1">
                                                        <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ __('Email') }}" name="email">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-envelope"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <!-- <label>{{ __('Password') }}</label> -->
                                                    <div class="input-group input-group--style-1">
                                                        <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ __('Password') }}" name="password">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-lock"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <!-- <label>{{ __('Confirm Password') }}</label> -->
                                                    <div class="input-group input-group--style-1">
                                                        <input type="password" class="form-control" placeholder="{{ __('Confirm Password') }}" name="password_confirmation">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-lock"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-box bg-white mt-4">
                                <div class="form-box-title px-3 py-2">
                                    {{__('Verification info')}}
                                </div>
                                @php
                                    $verification_form = \App\ShippingagentConfig::where('type', 'verification_form')->first()->value;
                                @endphp
                                <div class="form-box-content p-3">
                                    @foreach (json_decode($verification_form) as $key => $element)
                                        @if ($element->type == 'text')
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label>{{ $element->label }} <span class="required-star">*</span></label>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="{{ $element->type }}" class="form-control mb-3" placeholder="{{ $element->label }}" name="element_{{ $key }}" required>
                                                </div>
                                            </div>
                                        @elseif($element->type == 'file')
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label>{{ $element->label }}</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="{{ $element->type }}" name="element_{{ $key }}" id="file-{{ $key }}" class="custom-input-file custom-input-file--4" data-multiple-caption="{count} files selected" required/>
                                                    <label for="file-{{ $key }}" class="mw-100 mb-3">
                                                        <span></span>
                                                        <strong>
                                                            <i class="fa fa-upload"></i>
                                                            {{__('Choose file')}}
                                                        </strong>
                                                    </label>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'select' && is_array(json_decode($element->options)))
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label>{{ $element->label }}</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <select class="form-control selectpicker" data-minimum-results-for-search="Infinity" name="element_{{ $key }}" required>
                                                            @foreach (json_decode($element->options) as $value)
                                                                <option value="{{ $value }}">{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'multi_select' && is_array(json_decode($element->options)))
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label>{{ $element->label }}</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <select class="form-control selectpicker" data-minimum-results-for-search="Infinity" name="element_{{ $key }}[]" multiple required>
                                                            @foreach (json_decode($element->options) as $value)
                                                                <option value="{{ $value }}">{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($element->type == 'radio')
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label>{{ $element->label }}</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        @foreach (json_decode($element->options) as $value)
                                                            <div class="radio radio-inline">
                                                                <input type="radio" name="element_{{ $key }}" value="{{ $value }}" id="{{ $value }}" required>
                                                                <label for="{{ $value }}">{{ $value }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-styled btn-base-1">{{__('Save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

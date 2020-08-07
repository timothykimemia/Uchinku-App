@extends('layouts.app')

@section('content')
    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{__('Shippingagent Users')}}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{__('Name')}}</th>
                    <th>{{__('Phone')}}</th>
                    <th>{{__('Email Address')}}</th>
                    <th>{{__('Verification Info')}}</th>
                    <th>{{__('Approval')}}</th>
                    <th>{{ __('Due Amount') }}</th>
                    <th width="10%">{{__('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($shippingagent_users as $key => $shippingagent_user)
                    @if($shippingagent_user->user != null)
                        <tr>
                            <td>{{ ($key+1) + ($shippingagent_users->currentPage() - 1)*$shippingagent_users->perPage() }}</td>
                            <td>{{$shippingagent_user->user->name}}</td>
                            <td>{{$shippingagent_user->user->phone}}</td>
                            <td>{{$shippingagent_user->user->email}}</td>
                            <td>
                                @if ($shippingagent_user->informations != null)
                                    <a href="{{ route('shippingagent_users.show_verification_request', $shippingagent_user->id) }}">
                                        <div class="label label-table label-info">
                                            {{__('Show')}}
                                        </div>
                                    </a>
                                @endif
                            </td>
                            <td>
                                <label class="switch">
                                    <input onchange="update_approved(this)" value="{{ $shippingagent_user->id }}" type="checkbox" <?php if($shippingagent_user->status == 1) echo "checked";?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                @if ($shippingagent_user->balance >= 0)
                                    {{ single_price($shippingagent_user->balance) }}
                                @endif
                            </td>
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{__('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a onclick="show_payment_modal('{{$shippingagent_user->id}}');">{{__('Pay Now')}}</a></li>
                                        <li><a href="{{route('shippingagent_user.payment_history', encrypt($shippingagent_user->id))}}">{{__('Payment History')}}</a></li>
                                        {{-- <li><a onclick="confirm_modal('{{route('sellers.destroy', $shippingagent_user->id)}}');">{{__('Delete')}}</a></li> --}}
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
            <div class="clearfix">
                <div class="pull-right">
                    {{ $shippingagent_users->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-content">

            </div>
        </div>
    </div>

    <div class="modal fade" id="profile_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-content">

            </div>
        </div>
    </div>


@endsection

@section('script')
    <script type="text/javascript">
        function show_payment_modal(id){
            $.post('{{ route('shippingagent_user.payment_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#payment_modal #modal-content').html(data);
                $('#payment_modal').modal('show', {backdrop: 'static'});
                $('.demo-select2-placeholder').select2();
            });
        }

        function update_approved(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('shippingagent_user.approved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Approved sellers updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
        }

        // function sort_sellers(el){
        //     $('#sort_sellers').submit();
        // }
    </script>
@endsection

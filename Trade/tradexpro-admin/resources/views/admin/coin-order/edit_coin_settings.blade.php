@extends('admin.master',['menu'=>'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Coin')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                @if($item->network == BITGO_API)
                <div class="header-bar">
                    <div class="table-title">

                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2">
                            <a href="{{route('adminCoinApiSettings',['tab' => 'bitgo'])}}">{{__('Bitgo Api Setting')}}</a>
                        </div>
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{route('adminAdjustBitgoWallet',encrypt($item->coin_id))}}">{{__('Adjust Bitgo Wallet')}}</a>
                        </div>
                    </div>
                </div>
                @endif
                @if($item->network == ERC20_TOKEN || $item->network == BEP20_TOKEN)
                <div class="header-bar">
                    <div class="table-title">

                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2">
                            <a href="{{route('adminCoinApiSettings',['tab' => 'erc20'])}}">{{__('Token Api Setting')}}</a>
                        </div>
                    </div>
                </div>
                @endif
                <div class="profile-info-form">
                    <div>
                        {{Form::open(['route'=>'adminSaveCoinSetting', 'files' => true])}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Type')}}</div>
                                        <p class="form-control">{{$item->coin_type}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin API')}}</div>
                                        <p class="form-control">{{api_settings($item->network)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($item->network == BITCOIN_API)
                            @include('admin.coin-order.include.bitcoin')
                        @elseif($item->network == BITGO_API)
                            @include('admin.coin-order.include.bitgo')
                        @else
                            @include('admin.coin-order.include.erc20')
                        @endif
                        <div class="row">
                            <div class="col-md-2">
                                @if(isset($item))<input type="hidden" name="coin_id" value="{{encrypt($item->coin_id)}}">  @endif
                                <button type="submit" class="btn theme-btn">{{$button_title}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                        @if($item->network == BITGO_API)
                            <hr>
                            <div class="custom-breadcrumb">
                                <div class="row">
                                    <div class="col-9">
                                        <ul>
                                            <li class="active-item">{{ __("Add Bitgo Webhook") }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{Form::open(['route'=>'webhookSave', 'files' => true])}}
                            @include('admin.coin-order.include.webhook')
                            <div class="row">
                                <div class="col-md-2">
                                    @if(isset($item))<input type="hidden" name="coin_id" value="{{encrypt($item->coin_id)}}">  @endif
                                    <button type="submit" class="btn theme-btn">{{__("Update Webhook")}}</button>
                                </div>
                            </div>
                            {{Form::close()}}
                        @endif
                    </div>
                </div>
            </div>
            @if ($item->network == ERC20_TOKEN || $item->network == BEP20_TOKEN)
                <div class="col-md-12">
                    <div class="row float-right">
                        <div class="col-md-12">
                            <a href="javascript:" class="btn theme-btn float-right" onclick="check_wallet_address()">{{__('Check Wallet Address')}}</a>
                        </div>
                        <div class="col-md-12">
                            <h3 class="text-danger mt-2 float-right" id="check_wallet_address_message"></h3>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
<script>
    function check_wallet_address()
    {
        var wallet_key = $('#wallet_key').val();
        
        if(wallet_key !== '')
        {
            $.ajax({
                type: "POST",
                url: "{{ route('check_wallet_address') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'wallet_key': wallet_key,
                    'coin_type': '{{$item->coin_type}}'
                },
                success: function (data) {
                    $('#check_wallet_address_message').empty().text(data.message);
                }
            });

            
        }else{
            $('#check_wallet_address_message').empty().text('{{__('please, Insert wallet key First')}}');
        }
        
    }
</script>
@endsection

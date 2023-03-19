<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Email Setup')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveEmailSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Email Host')}}</label>
                    <input class="form-control" type="text" name="mail_host"
                           placeholder="{{__('Host')}}" value="{{$settings['mail_host']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Email Port')}}</label>
                    <input class="form-control" type="text" name="mail_port"
                           placeholder="{{__('Port')}}" value="{{$settings['mail_port']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Email Username')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="mail_username"
                           placeholder="{{__('Username')}}"
                           value="{{ $settings['mail_username'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Email Password')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="password" name="mail_password"
                           placeholder="{{__('Password')}}"
                           value="{{$settings['mail_password']}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Mail Encryption')}}</label>
                    <input class="form-control" type="text" name="mail_encryption"
                           placeholder="{{__('Encryption')}}"
                           value="{{isset($settings['mail_encryption']) ? $settings['mail_encryption'] : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Mail Form Address')}}</label>
                    <input class="form-control" type="text" name="mail_from_address"
                           placeholder="{{__('Mail from address')}}"
                           value="{{isset($settings['mail_from_address']) ? $settings['mail_from_address'] : ''}}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>

<div class="header-bar mt-5">
    <div class="table-title">
        <h3>{{__('Check Email Configuration')}}</h3>
    </div>
</div>
<div class="profile-info-form mt-5">
        <form action="{{route('testmailsend')}}" method="post">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12  mt-20">
                    <div class="form-group">
                        <label for="#">{{__('Email Address')}}</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-12 mt-20">
                    <button type="submit" class="button-primary theme-btn">{{__('Send Test Mail')}}</button>
                </div>
            </div>
        </form>
    </div>


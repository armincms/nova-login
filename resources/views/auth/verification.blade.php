@extends('nova::auth.layout')

@section('content')

@include('nova::auth.partials.header')

<form
    class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
    method="POST"
    action="{{ $auth->route('verification') }}"
>
    {{ csrf_field() }}

    @component('nova::auth.partials.heading')
        {{ __("Please fill below form for receiving code") }}
    @endcomponent 

    @if ($errors->any())
    <p class="text-center font-semibold text-danger my-3">
        @if ($errors->has('mobile'))
            {{ $errors->first('mobile') }} <br />
        @else
            {{ $errors->first() }}
        @endif
    </p>
    @endif
   
    <div class="mb-6 {{ $errors->has('mobile') ? ' has-error' : '' }}"> 
        <label class="block font-bold mb-2" for="mobile">
            {{ __("Mobile Number") }}
        </label> 
        <input 
            class="form-control form-input w-full {{ $errors->has('mobile') ? 'border-danger' : 'form-input-bordered' }}" 
            id="mobile" type="text" name="mobile" value="{{ old('mobile') }}"
            autofocus required>
    </div> 

    <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
        {{ __('Send') }}
    </button>
</form>
@endsection

@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Marketing Campaign</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('marketing.campaigns.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('marketing.campaigns.update', $campaign) }}" method="POST">
            @csrf
            @method('PUT')
            @include('marketing.campaigns._form')
        </form>
    </div>
</section>
@endsection

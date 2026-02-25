@if (session('success_message'))
<div class="alert alert-success alert-dismissible">
    <button class="close close-sm" data-dismiss="alert" type="button" aria-label="Close"><span aria-hidden="true">×</span>
    </button>
    <h4>
        <i class="icon fa fa-check">
        </i>
        Success!
    </h4>
    <p>
        {{ session('success_message') }}
    </p>
</div>
@endif

@if (session('warning_message'))
<div class="alert alert-warning alert-dismissible">
    <button class="close close-sm" data-dismiss="alert" type="button">
            <span aria-hidden="true">×</span>
    </button>
    <h4>
        <i class="icon fa fa-warning">
        </i>
        Warning!
    </h4>
    {{ session('warning_message') }}
</div>
@endif

@if (session('error_message'))
<div class="alert bg-red alert-dismissible">
    <button class="close close-sm" data-dismiss="alert" type="button">
            <span aria-hidden="true">×</span>
    </button>
    <h4>
        <i class="icon fa fa-ban">
        </i>
        Error!
    </h4>
    {{ session('error_message') }}
</div>
@endif

@if (count($errors) > 0)
<div class="alert bg-red alert-dismissible" role="alert">
    <button type="button" class="close fade-in" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    <h4>
        <i class="icon fa fa-ban">
        </i>
        Error!
    </h4>
    <ul>
        @foreach ($errors->all() as $error)
        <li>
            {{ $error }}
        </li>
        @endforeach
    </ul>
</div>
@endif
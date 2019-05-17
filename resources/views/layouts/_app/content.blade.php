<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        @section('title')
            @include('components/title', [
                'title'=>'Заголовок страницы',
                'description'=>'Описание страницы'
             ])
        @show
        @section('breadcrumbs')
            {{ Breadcrumbs::render('home') }}
        @show
    </section>

    <!-- Main content -->
    <section class="content container-fluid">
        @yield('content')
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
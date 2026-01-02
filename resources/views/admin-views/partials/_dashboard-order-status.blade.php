<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card" href="{{route('admin.customer.list')}}">
        <h5 class="business-analytics__subtitle">Total Users</h5>
        <h2 class="business-analytics__title">{{ $data['customer'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-customer.png')}}" class="business-analytics__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card" href="{{route('admin.restaurant.index',['all'])}}">
        <h5 class="business-analytics__subtitle">Total Restaurants</h5>
        <h2 class="business-analytics__title">{{ $data['restaurant'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" width="30" height="30" class="business-analytics__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics get-view-by-onclick card" href="">
        <h5 class="business-analytics__subtitle">Total Points Awarded</h5>
        <h2 class="business-analytics__title">{{ $data['points'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-stores.png')}}" class="business-analytics__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card">
        <h5 class="business-analytics__subtitle">Total Redemptions</h5>
        <h2 class="business-analytics__title">{{ $data['redemption'] }}</h2>
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-product.png')}}" class="business-analytics__img" alt="">
    </a>
</div>
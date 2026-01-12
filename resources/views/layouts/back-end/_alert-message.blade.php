<div class="alert--container active">
   

    <div class="alert alert--message-2 alert-dismissible fade show product-limited-stock-alert">
        <div class="d-flex">
            <img width="28" class="align-self-start image" src="" alt="">
        </div>
        <div class="w-0 text-start">
            <h6 class="title text-truncate"></h6>
            <span class="message">
            </span>
            <div class="d-flex justify-content-between gap-3 mt-2">
                <a href="javascript:" class="text-decoration-underline text-capitalize product-stock-alert-hide">{{translate('do_not_show_again')}}</a>
                <a href="javascript:" class="text-decoration-underline text-capitalize product-list">{{translate('click_to_view')}}</a>
            </div>
        </div>
        <button type="button" class="close position-relative p-0 product-stock-limit-close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="alert alert--message-3 alert--message-for-pos border-bottom alert-dismissible fade show">
        <img width="28" src="{{ dynamicAsset(path: 'public/assets/back-end/img/warning.png') }}" alt="">
        <div class="w-0">
            <h6>{{ translate('Warning').'!'}}</h6>
            <span class="warning-message"></span>
        </div>
        <button type="button" class="close position-relative p-0 close-alert--message-for-pos">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>


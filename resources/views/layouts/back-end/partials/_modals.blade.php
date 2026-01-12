
<style>
    #newRestaurantModalBody p {
        margin: 0.5rem 0;
        padding: 0.3rem 0;
        border-bottom: 1px dashed #dee2e6;
    }

    #newRestaurantModalBody p:last-child {
        border-bottom: none;
    }

    .modal-header i {
        color: #fff;
    }

    .modal-footer .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .modal-footer .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }

    .modal-footer .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
</style>




<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{translate('ready_to_Leave').'?'}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">{{translate('Select_Logout_below_if_you_are_ready_to_end_your_current_session').'.'}}</div>
            <div class="modal-footer">
                <form action="{{route('admin.logout')}}" method="post">
                    @csrf
                    <button class="btn btn-danger" type="button" data-dismiss="modal">{{translate('cancel')}}</button>
                    <button class="btn btn--primary" type="submit">{{translate('logout')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

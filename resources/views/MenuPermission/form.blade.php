@section('subheader')
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-2">
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Menu Permission</h5>
                <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200"></div>
            </div>
        </div>
    </div>
@endsection

<div class="">
    <form class="forms-sample">
        <div class="card card-custom custom-card" style="margin-top: 3rem">
            <div class="card-header">
                <h3 class="card-title">Select User And Permission</h3>
            </div>
            <div class="card-body choose-services pt-4">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('name', 'User Name', ['class' => 'star required']) !!}
                        <?php
                        $emp_list = App\Http\Helper\Admin\Helpers::getEmpListPermission();
                        if(isset($_POST['user_id'])){
                            $isset_user = $_POST['user_id'];
                        } else {
                            $isset_user = null;
                        }
                        ?>
                        {!! Form::select('user_id', $emp_list, $isset_user, [
                            'class' => 'form-control js-designation',
                            'id' => 'user_id',
                        ]) !!}
                    </div>
                </div>


                <div class="form-group ml-4 ">
                    <div class="checkbox-inline checkbox" style="width: 100%">
                        <?php
                        $main_menu = App\Models\Menu::where('status', 'Active')->orderby('menu_name')->get();
                        if (isset($_POST['user_id'])) {
                            $user_permission = App\Models\MainMenuPermission::select('parent_id')->where('user_id', $_POST['user_id'])->first();
                            if (!empty($user_permission)) {
                                $uList = explode(',', $user_permission->parent_id);
                                $str = "'" . implode("','", $uList) . "'";
                                $get_user_submenu = App\Models\SubMenuPermission::select('sub_menu_id')->where('user_id', $_POST['user_id'])->get()->toArray();
                                $sub_list = collect($get_user_submenu);
                            }
                        } else {
                            $uList = [];
                        }
                        ?>

                        @foreach ($main_menu as $m_permission)
                            <label class="checkbox mt-1 font-size-20">
                                <input type='hidden' id="parent" name="parent" value="{{ request()->parent }}" />
                                <input type='hidden' id="child" name="child" value="{{ request()->child }}" />
                                <input type="checkbox" value="{{ $m_permission->id }}" id="{{ $m_permission->id }}"
                                    <?php if (!empty($uList)) {
                                        if (in_array($m_permission->id, $uList)) {
                                            echo 'checked';
                                        }
                                    } ?> />
                                {!! Form::label('s_active', ucwords($m_permission->menu_name), [
                                    'class' => '',
                                    'style' => 'font-size: 15px !important;font-weight: 500;',
                                ]) !!}<span></span>
                            </label>
                            <br>
                            <?php
                            $sub_menu = App\Models\SubMenu::where('menu_id', $m_permission->id)->get();
                            ?>
                            <div class="row mb-3 mt-2" style="border-bottom:1px dashed #ccc;padding-bottom:5px;">
                                @foreach ($sub_menu as $s_menu)
                                    <div class="col-xl-2 col-sm-2">
                                        <div class="mb-4">
                                            <label class="card-radio-label mb-2">
                                                <input type="checkbox" class="card-radio-input"
                                                    value="sub_{{ $m_permission->id }}_{{ $s_menu->id }}"
                                                    id="{{ $s_menu->id }}" <?php if (!empty($sub_list)) {
                                                        if (count($sub_list->where('sub_menu_id', $s_menu->id)) == 1) {
                                                            echo 'checked';
                                                        } else {
                                                            echo 'No in';
                                                        }
                                                    } ?>>

                                                <div class="card-radio">
                                                    <div>
                                                        <span>{{ ucwords($s_menu->sub_menu_name) }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-12 py-1 card-footer pl-1 mt-8 text-left">
                        <button type="button"
                            class="btn btn-primary font-weight-bold text-uppercase btn-space-around mr-1 menu_permission_submit"
                            id="alert_msg">Submit</button>
                        <button type="reset"
                            class="btn btn-secondary font-weight-bold text-uppercase btn-space-around">Reset</button>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

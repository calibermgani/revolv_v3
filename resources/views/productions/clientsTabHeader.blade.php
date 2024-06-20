
    <ul class="nav nav-tabs nav-tabs-line" id="myTab">
        <li class="nav-item">
            <a class="nav-link active font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_1"
                style="font-size:16px">Assigned</a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_2"
                style="font-size:16px">Pending</a>
                   {{-- <a class="nav-link font-size-lg text-primary" href="{{ url('projects_pending/' . request()->id . '/' . request()->clientName.'/') }}?parent={{ request()->parent }}&child={{ request()->child }}"
                            style="font-size: 16px" id="pending_tab">Pending</a> --}}
        </li>
        <li class="nav-item">
            <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_3"
                style="font-size:16px">On Hold</a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_4"
                style="font-size:16px">Completed</a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_4"
                style="font-size:16px">Rework</a>
        </li>
        @if ($empDesignation == 'Administrator')
            <li class="nav-item">
                <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_5"
                    style="font-size:16px">Duplicate</a>
            </li>
        @endif
    </ul>



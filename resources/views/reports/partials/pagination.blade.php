       <div class="d-flex justify-content-between align-items-center w-100" >
                <!-- Left side -->
                <div class="ml-8">
                    Showing {{ $completedProjectDetails->firstItem() ?? 0 }}
                    to {{ $completedProjectDetails->lastItem() ?? 0 }} of
                    {{ $completedProjectDetails->total() }} entries
                </div>

                <!-- Right side -->
                <div class="mr-3">
                    {!! $completedProjectDetails->appends(request()->except(['page']))->links() !!}
                </div>
            </div>

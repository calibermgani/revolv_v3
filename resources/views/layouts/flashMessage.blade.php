@if ($message = Session::get('success'))
<script>
$(document).ready(function(){
	js_notification('success','<?php echo $message; ?>');
})
</script>
@endif

@if ($message = Session::get('error'))
<script>
$(document).ready(function(){
	js_notification('error','<?php echo $message; ?>');
})
</script>

@endif


<!-- Modal -->
<div id="entreFechas" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">@lang("messages.buttons.avisar") {{trans('models.modelos.'.$panel->getModel()) }}</h4>
      </div>
      <div class="modal-body">
		  <form id="formFechas" action="#" method="POST">
			 {{ csrf_field() }}
			 <label class="control-label" for="desde">@lang("messages.generic.desde"):</label>
			 <input type='text' id="desde" name="desde" class="form-control date" value="{{hoy('d/m/Y')}}"></input>
              <label class="control-label" for="hasta">@lang("messages.generic.hasta"):</label>
              <input type='text' id="hasta" name="hasta" class="form-control date" value="{{hoy('d/m/Y')}}"></input>
		  </form>
      </div>
        
      <div class="modal-footer">
		<button type="submit" form="formFechas" class="btn btn-primary">@lang("messages.buttons.confirmar")</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang("messages.buttons.cancel")</button>
      </div>
    </div>

  </div>
</div>
<script src="/js/datepicker.js"></script>


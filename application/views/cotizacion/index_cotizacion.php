<div class="white-area-content">
    <div class="row">
        <div class="col-md-3">
            <a href="<?php echo site_url("cotizacion/operacion"); ?>" class="btn btn-primary ">
                <i class="mdi mdi-lead-pencil"></i>
                New Quote
            </a>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="block-area align-center">
                <h4 class="home-label">Cotizaciones registradas</h4>

                <table id="grid-data" class="table table-condensed table-hover table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="cotizacion_id" data-type="numeric">#</th>
                        <th data-column-id="autor">Autor</th>
                        <th data-column-id="created" data-formatter="">Creación</th>
                        <th data-column-id="origen">Origen</th>
                        <th data-column-id="destino">Destino</th>
                        <th data-column-id="cotizacion_id" data-formatter="link" data-sortable="false">Acciones</th>
                    </tr>
                    </thead>
                </table>
                <div class="clearfix"></div>
            </div>
            <div class="block-area">
                <?php echo lang("ctn_326") ?>
                <b><?php echo date($this->settings->info->date_format, $this->user->info->online_timestamp); ?></b>
            </div>
        </div>
    </div>
</div>
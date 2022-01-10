<div class="build_box flethan col-6">
    <div class="content_box row" style="padding-right: 3px;">
        <div class="col-7">
            <div class="row">
                <div class="col-1">
                    <div class="image hang2 tooltip2m" {if $Element.speed != 0}data-tooltip-content="{$LNG.fl_fleet_speed} {$Element.speed}"{/if}>
                        <a href="#" onclick="return Dialog.info({$Element.id})"><img src="{$dpath}gebaeude/{$Element.id}.gif" alt="{$Element.id}" /></a>
                    </div>
                </div>
                <div class="col-7">
                    <div class="hang3">
                        <span>{$LNG.tech.{$Element.id}}</span>
                    </div>
                </div>
                <div class="col-3 text-right">
                    <span class="hang4" id="ship{$Element.id}_value">{$Element.count|number}</span>
                </div>
            </div>
        </div>
		<div class="col-5">
            {if $Element.speed != 0}
                <div class="fl_fllets_rows_input_div input-group">
                    <div class="input-group-prepend">
                        <div onclick="minShip('ship{$Element.id}');" class="minimhang input-group-text">Min</div>
                    </div>
                    <input class="countdots form-control inputhang" name="ship{$Element.id}" id="ship{$Element.id}_input" value="0" onkeyup="getReadeble({$Element.id}); fleetPoints();">
                    <div class="input-group-append">
                        <div onclick="maxShip('ship{$Element.id}');" class="maximhang input-group-text">Max</div>
                    </div>
                </div>
            {else}
                <div class="fl_fllets_rows_input_div hang1"></div>
            {/if}
        </div>
    </div>
</div>

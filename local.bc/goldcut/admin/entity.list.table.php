<div class='admin-filter'>
	<form action="/goldcut/admin/" method="GET">

<?php

		echo "<div class=floatinner>";
		/**
		$filter = "Taxonomy";
		$ckey = 'admin-selectors-html-'.$ENTITY->name;
		if (cacheAdminSelectors === true && $selectors = Cache::get($ckey))
		{
			$selectorsHtml = $selectors;
		}
		else
		{
			$selectorsHtml = '';
			foreach (Entity::each_managed_entity($filter) as $m => $es)
			{
				foreach($es as $e)
				{
				//	foreach ($ENTITY->related() as $rel)
				//	{
						//if ($rel->urn == $e->urn)
						if ($ENTITY->name == 'news')
						{
							$selected = array($_GET[$e->name]);
							//printlnd($selected);
							$m = new Message();
							$m->action = 'load';
							$m->urn = 'urn:'.$e->name;
							$eds = $m->deliver();
							$selectorsHtml .= "<div class=admin_taxonomy_nav>";
							$selectorsHtml .= Form::category_selectbox($e, $eds, $selected, array(''=>'во всех'));
							$selectorsHtml .= "</div>";
						}
				//	}
				}
			}
			Cache::put($ckey, $selectorsHtml);
		}
		echo $selectorsHtml;
		*/

        $ckey = 'cacheAdminSelectors';

		if (cacheAdminSelectors === true && $selectors = Cache::get($ckey, 60 * 15))
		{
			$selectorsHtml = $selectors;
		}
		else
		{
			$selectorsHtml = '';
			/*
			if ($ENTITY->treeview)
			{
				$selectorsHtml .= "<div class=admin_taxonomy_nav>";
				$selectorsHtml .= Form::parentSelector($ENTITY, null, null); // $eo->_parent, $eo->id
				$selectorsHtml .= "</div>";
			}
			*/
			foreach ($ENTITY->belongs_to() as $ebt)
			{
				$selected = array($_GET[$ebt->name]);
				//printlnd($selected);
				$m = new Message();
				$m->action = 'load';
				$m->urn = 'urn:'.$ebt->name;
                $m->last = 200;
                if ($ebt->defaultorder)
                {
                    $m->order = $ebt->defaultorder;
                }
				$eds = $m->deliver();
				$selectorsHtml .= "<div class=admin_taxonomy_nav>";
                $STRING_INALL = array('ru'=>'во всех', 'en'=>'in all');
				$selectorsHtml .= Form::category_selectbox($ebt, $eds, $selected, array(''=>$STRING_INALL[DEFAULT_LANG]));
				$selectorsHtml .= "</div>";
			}
			Cache::put($ckey, $selectorsHtml);
		}
		echo $selectorsHtml;

?>
		<div id="searchinputandsubmit" class="admin_taxonomy_nav FR">
			<input id="query" name="search" value="<?php echo $_GET['search']; ?>" />
			<input type=submit value="<?= $STRING_SEARCH[DEFAULT_LANG] ?>" class="submit button">
			<input type=hidden name=urn value=urn:<?php echo $E; ?>>
			<input type=hidden name=action value="list">
		</div>
		</div><br style='clear: both;'>
	</form>
</div>


<div id="result" style="font-family: monospace;"></div>







<table border="0" cellspacing="0" cellpadding="3" class="dataTable" data-hosturn="<?= $ds->entity ?>">
<tbody>

<?php
include "entity.list.table.rows.php";
?>

</tbody>
</table>

<div id="multydelete" class="">
    <input type="button" value="УДАЛИТЬ ВЫБРАННЫЕ" id="multydelete_action" class="submit button redbutton TM1 BM1">
</div>

<script>

    function res(d)
    {
        this.resolve(d);
    }


    // multydelete
    var atleastoneselected = 0;
    var selectsX = bycss('.multyactionurn');
    hide(id('multydelete'));
    for (var i =0; i < selectsX.length; i++) {
        Event.add(selectsX[i], 'click', function() {
            atleastoneselected = 0;
            for (var j = 0; j < selectsX.length; j++) {
                if (selectsX[j].checked) atleastoneselected++;
            }
            if (atleastoneselected > 0) show(id('multydelete'));
            else hide(id('multydelete'));
        });
    }
    // ACTION DEL SELECTED
    Event.add(id('multydelete_action'), 'click', function() {
        var selectsX = bycss('.multyactionurn');
        var todel = [];
        for (var j = 0; j < selectsX.length; j++) {
            if (selectsX[j].checked) {
                var selectedURN = selectsX[j].getAttribute('data-urn');
                todel.push(selectedURN);
            }
        }

        if (todel.length > 0) {
            var alldoned = [];
            for (var z = 0; z < todel.length; z++) {
                var selectedURN = todel[z];
                m = {};
                m.action = 'delete';
                m.urn = selectedURN;
                var deferred = when.defer();
                alldoned.push(deferred.promise);
                ajax('/goldcut/admingate.php', res.bind(deferred), {}, 'POST', m);
            }
            when.all(alldoned).then(function(all) {
                window.location.reload();
            });
        }

    });



    var selectableText = document.querySelectorAll('input.multyactionurn');
    var state = false;

    Event.add(id('setunsetall'), 'click', function() {
        state = (state) ? false : true;
        for (var j=0; j<selectableText.length;j++) {
            selectableText[j].checked = state;
        }
    });

    for (var j=0; j<selectableText.length;j++) {
        var selectableTextBlock = selectableText[j];
        Event.add(selectableTextBlock, 'click', function() {
            //console.log(this);
            addClass(id('searchinputandsubmit'), 'hide');
        });
    }



    var selects = bycss('select');
    for (var i =0; i< selects.length; i++)
    {
        var sel = selects[i];
        sel.ordered = i;
        Event.add(sel, "change", function(e) {
            var selIndex = this.selectedIndex;
            var setREL = this.options[selIndex].value;
            //console.log(setREL);
            //setSelectedInGlobalSelectsFromIndex(this.ordered, selIndex, this.getAttribute('data-eclass'))

            var selects = bycss('.multyactionurn');
            var alldone = [];
            for (var i =0; i< selects.length; i++) {
                var sel = selects[i];
                if (sel.checked) {
                    //console.log()
                    var selectedURN = sel.getAttribute('data-urn');
                    m = {};
                    m.action = 'update';
                    m.urn = selectedURN;
                    var u = new URN(setREL);
                    m[u.entity] = setREL;
                    //console.log(m);
                    var deferred = when.defer();
                    alldone.push(deferred.promise);
                    ajax('/goldcut/admingate.php', res.bind(deferred), {}, 'POST', m);
                }
            }

            when.all(alldone).then(function(all) {
                //console.log(all);
                //alert('All done');
            });

        })
    }

</script>
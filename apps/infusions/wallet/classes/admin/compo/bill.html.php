<?php
require_once dirname(__FILE__).'/../../../../../maincore.php';
require_once WALLET.'autoloader.php';
/*
 * $javascript = "
        <script>
        $('#add_row').bind('click', function(e) {
            e.preventDefault();
            var tblRows = document.getElementById('orderFormTbl').getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;
            $.ajax({
                    type: 'POST',
                    url: '".FUSION_ROOT.WALLET."class/admin/compo/bill.html.php',
                    data: {'rows': tblRows },
                    dataType: 'html',
                    success: function(data) {
                        $('#bill_table').append(data);
                    },
                    error: function() {
                        alert('fail');
                    }
                });
        });
        $('#del_row').bind('click', function(e) {
            e.preventDefault();
            $('input[name^=bill_id]:checked').map(function() {
                $('#row-'+this.value).remove();
            });
        });
        </script>
        ";
        $html .= form_button('add_row', 'Add Row', 'add_row', ['class'=>'m-r-10 btn-default']);
        $html .= form_button('del_row', 'Delete Row', 'del_row');


        add_to_jquery(str_replace(['<script>', '</script>'], ['',''], $javascript));

 */
$result = dbquery("SELECT item_id, item_type, item_title FROM ".DB_WALLET_ITEMS." ORDER BY item_title ASC, item_id ASC");
$productOpts = [];
while ($data = dbarray($result)) {
    $productOpts[$data['item_id']] = $data['item_type'];
}
$count = stripinput($_POST['rows']);

$html .= "<tr id='row-$count'>\n";
$html .= "<td>".form_checkbox('bill_id ['.$count.']', '', '', ['value'=>$count])."</td>\n";
$html .= "<td>\n";
$html .= form_select('item_id['.$count.']', '', '',
                     [
                         'options' => $productOpts,
                         'input_id' => 'item-id-'.$count,
                         'inline'=>TRUE, 'inner_width'=>'300px', 'required' => TRUE]);
$html .= "</td>\n<td>\n";
$html .= form_text('item_quantity['.$count.']', '', '',
                   [
                       'required' => TRUE,
                       'inline'=>TRUE,
                       'type'=>'number',
                       'inner_width'=>'200px',
                       'append' => TRUE,
                       'append_value' => 'Units',
                       'input_id' => 'item-quantity-'.$count
                   ]);
$html .= "</td>\n<td>\n";
$html .= form_text('item_value['.$count.']', '', '',
                   [
                       'required' => TRUE,
                       'input_id' => 'item-value-'.$count,
                       'inline'=>TRUE,
                       'inner_width'=>'200px',
                       'type'=>'number',
                       'prepend' => TRUE,
                       'prepend_value' => \Wallet\Model::walletSettings('wallet_base_currency')
                   ]
);
$html .= "</td>\n<td>\n";
$html .= form_text('item_total['.$count.']', '', '',
                   [
                       'required' => TRUE,
                       'input_id' => 'item-total-'.$count,
                       'inline'=>TRUE,
                       'inner_width'=>'200px',
                       'type'=>'price',
                       'prepend' => TRUE,
                       'prepend_value' => \Wallet\Model::walletSettings('wallet_base_currency')
                   ]
);
$html .= "</td>\n<td>\n";
$html .= form_checkbox('item_tangible['.$count.']', '', '', ['input_id'=>'item-tangible-'.$count]);
$html .= "</td>\n<td>".form_checkbox('item_taxable['.$count.']', '', '', ['input_id'=>'item-taxable-'.$count])."</td>\n";
$html .= "</td>\n</tr>\n";

echo $html;
echo \PHPFusion\OutputHandler::$pageFooterTags;
$jquery_tags = \PHPFusion\OutputHandler::$jqueryTags;
echo "<script>\n";
echo $jquery_tags;
echo "</script>\n";
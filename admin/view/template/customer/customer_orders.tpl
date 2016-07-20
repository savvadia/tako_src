<table class="table table-bordered">
  <thead>
    <tr>
      <td class="text-left"><?php echo $column_order_id; ?></td>
      <td class="text-left"><?php echo $column_status; ?></td>
      <td class="text-left"><?php echo $column_date_added; ?></td>
      <td class="text-left"><?php echo $column_date_modified; ?></td>
      <td class="text-left"><?php echo $column_total; ?></td>
      <td class="text-left"><?php echo $column_action; ?></td>
    </tr>
  </thead>
  <tbody>
    <?php if ($orders) { ?>
    <?php foreach ($orders as $order) { ?>
    <tr>
      <td class="text-left">#<?php echo $order['id']; ?></td>
      <td class="text-left"><?php echo $order['status']; ?></td>
      <td class="text-left"><?php echo $order['date_added']; ?></td>
      <td class="text-left"><?php echo $order['date_modified']; ?></td>
      <td class="text-left"><?php echo $order['total']; ?></td>
      <td class="text-right">
          <a href="<?php echo $order['action_view']; ?>" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="View"><i class="fa fa-eye"></i></a> 
          <a href="<?php echo $order['action_edit']; ?>" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Edit"><i class="fa fa-pencil"></i></a> 
      </td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td class="text-center" colspan="6"><?php echo $text_no_results; ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>


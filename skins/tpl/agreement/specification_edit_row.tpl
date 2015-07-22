<tr>
    <td><input agregated="no" row_id="<?php echo $row['id']; ?>" type="checkbox" style="cursor:pointer;"></td>
    <td><?php echo ++$row_num; ?></td>
    <td managed="text" bd_row_id="<?php echo $row['id']; ?>" bd_field="name"><?php echo $row['name']; ?></td>
    <!--<td managed="text">&nbsp;</td>-->
    <td class="quantity" row_id="<?php echo $row['id']; ?>" field="quantity"><?php echo $row['quantity']; ?></td>
    <td class="price" row_id="<?php echo $row['id']; ?>" field="price"><?php echo $row['price']; ?></td>
    <td class="currensy">p.</td>
    <td class="price"><?php echo $row['summ']; ?></td>
    <td class="currensy">p.</td>
</tr>
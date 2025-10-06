<div class="row kitchen-tab">
    <?php
    $row = count($tableinfo);
    $numOfCols = 3;
    $rowCount = 0;
    $bootstrapColWidth = $row / $numOfCols;
    foreach ($tableinfo as $table) { ?>

        <div class="col-md-4">
            <div class="info_part <?php if ($table['sum'] >= $table['person_capicity']) {
                                        echo 'booked';
                                    } ?>">
                
                <div class="extra_elem" style="margin-bottom: 1rem;">
                     <?php 
                        $max= $table['person_capicity'] - $table['sum'];
                     ?>               
                    <form  style="width:100%">
                        <button type="button" onclick="checktable(<?php echo $table['tableid']; ?>)" class="btn add_button pull-left"><?php echo display('select_this_table') ?> :  # <?php echo $table['tablename'] ?>  </button>
                        
                        <div class="d-flex pull-right">
                             <input type="number" style="width:8rem" min="1" max="<?php echo $max ?>" value="<?php echo $max ?>" class="form-control" placeholder="<?php echo display('person') ?>" name="person-<?php echo $table['tableid']; ?>" id="person-<?php echo $table['tableid']; ?>">
                              
                            <strong style="padding:.5rem">
                                <span> / </span>  <?php echo $table['person_capicity'] ?> <?php echo  display('seat') ?>  
                            </strong>
                        </div>
                        
                    </form>

                  
                </div>
                  <?php if (!empty($table['table_details'])) { ?>
                        <button class="btn btn-clear" onClick="deleterow_table('9999',<?php echo $table['tableid']; ?>)"><?php echo display('clear') ?></button>
                    <?php } ?>
                <table class="table table-bordered table-modal table-info text-center">
                    <?php $table_count = count($table['table_details']); ?>
                    <thead <?php if ($table_count > 3) { ?> class="ws" <?php } ?>>
                        <tr>
                            <th><?php echo display('ord'); ?></th>
                            <th><?php echo display('seat_time'); ?></th>
                            <th><?php echo display('person'); ?></th>
                            <th><?php echo display('action'); ?></th>

                        </tr>
                    </thead>
                    <tbody id="table-tbody-<?php echo $table['tableid']; ?>">
                        <?php if (!empty($table['table_details'])) {
                            foreach ($table['table_details'] as $table_details) {
                        ?>
                                <tr id="table-tr-<?php echo $table_details->id; ?>">
                                    <td scope="row"><?php echo $table_details->order_id; ?></td>
                                    <td><?php echo $table_details->time_enter; ?></td>
                                    <td><?php echo $table_details->total_people; ?></td>
                                    <td>
                                        <button class="btn btn-del" onClick="deleterow_table(<?php echo $table_details->id; ?>)"><i class="ti-trash"></i></button>
                                    </td>
                                </tr>
                            <?php
                            } //end foreach
                        } //end if
                        else {
                            ?>
                            <tr>
                                <td>
                                    <h6><?php echo display('no_customer'); ?></h6>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>


                    </tbody>
                </table>
                
            </div>
        </div>
    <?php
        $rowCount++;
        if ($rowCount % $numOfCols == 0) echo '</div><div class="row kitchen-tab">';
    } ?>
</div>
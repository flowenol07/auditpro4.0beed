<div class="card apcard mb-4 audit_container_div">
    <div class="card-header">
        Authority: <?php echo $data['data']['authorityData'] -> name; ?>
    </div>

    <div class="card-body">
        <?php
            $res = generate_compliance_asses_table($data); 
            echo $res['mrk'];        
        ?>
    </div>

</div>
<?php $me = $this->me; ?>

<div class="container-fluid">

    <h1><?= $me->pageHeading ?></h1>

    <p>
        Welcome, 
        <?= \Core\Session::get('emp_details')['emp_name'] ?? 'Support User'; ?> 👋
    </p>

    <hr>

    <div class="row">

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h4>Total Tickets</h4>
                <h2>120</h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h4>Open Tickets</h4>
                <h2>35</h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h4>Resolved Tickets</h4>
                <h2>70</h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h4>Pending Followups</h4>
                <h2>15</h2>
            </div>
        </div>

    </div>

</div>

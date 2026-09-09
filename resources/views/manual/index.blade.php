@extends('layouts.admin')

@section('content')
<div class="manual-hero">
    <div>
        <p class="manual-kicker">START HERE</p>
        <h1>Victo OMS User Manual</h1>
        <p class="manual-intro">A simple guide for moving every order from customer brief to completed delivery.</p>
    </div>
    <div class="manual-hero__icon"><i class="fas fa-book-open"></i></div>
</div>

<div class="manual-grid">
    <aside class="manual-nav">
        <p>IN THIS GUIDE</p>
        <a href="#basics">Getting started</a>
        <a href="#owner-admin">Owner and admin</a>
        <a href="#designer">Designer</a>
        <a href="#cameraman">Cameraman</a>
        <a href="#statuses">Order statuses</a>
        <a href="#checks">Final checks</a>
    </aside>

    <div class="manual-content">
        <section id="basics" class="manual-section">
            <span class="manual-section__number">01</span>
            <div>
                <h2>Getting started</h2>
                <p>Sign in using your registered email and password. Your sidebar is tailored to your role. Use the bell in the top header to check assignments, approvals, revisions and photo-session updates.</p>
                <div class="manual-steps">
                    <div><b>1</b><span><strong>Use the sidebar</strong> to open your workspace.</span></div>
                    <div><b>2</b><span><strong>Search orders</strong> by order number or customer when you need a specific record.</span></div>
                    <div><b>3</b><span><strong>Open View Order</strong> to see items, files, timeline and next available actions.</span></div>
                </div>
            </div>
        </section>

        <section id="owner-admin" class="manual-section">
            <span class="manual-section__number">02</span>
            <div>
                <h2>Owner and administrator workflow</h2>
                <p>Owners and administrators manage customers and orders. Owners additionally approve designs, manage fulfilment and access reports.</p>
                <div class="manual-role-cards">
                    <article><i class="fas fa-user-plus"></i><h3>Create customer</h3><p>Open Customers, add the contact details and save before creating the order.</p></article>
                    <article><i class="fas fa-cart-plus"></i><h3>Create order</h3><p>Select a customer, add items, price, due date, delivery method, brief and references.</p></article>
                    <article><i class="fas fa-check-double"></i><h3>Review design</h3><p>Owner reviews files on a Pending Approval order, then approves or requests a revision.</p></article>
                </div>
                <p class="manual-note"><strong>Tip:</strong> Check the customer brief, due date and attachments before every approval or status update.</p>
            </div>
        </section>

        <section id="designer" class="manual-section">
            <span class="manual-section__number">03</span>
            <div>
                <h2>Designer workflow</h2>
                <p>Open <strong>My Tasks</strong>, select an assigned order and review all order details before beginning. Select <strong>Start Task</strong> when work starts, upload the design file, then choose <strong>Submit for Approval</strong> when it is ready.</p>
                <ol class="manual-timeline">
                    <li><span>01</span><div><strong>Review</strong><small>Read the brief, items, due date and reference files.</small></div></li>
                    <li><span>02</span><div><strong>Design</strong><small>Start the task and create the approved version.</small></div></li>
                    <li><span>03</span><div><strong>Upload</strong><small>Use clear filenames that include the order number and version.</small></div></li>
                    <li><span>04</span><div><strong>Submit</strong><small>Send the completed file for owner approval. Respond to revisions on the same order.</small></div></li>
                </ol>
            </div>
        </section>

        <section id="cameraman" class="manual-section">
            <span class="manual-section__number">04</span>
            <div>
                <h2>Cameraman workflow</h2>
                <p>Use <strong>Photo Tasks</strong> to see orders ready at HQ or in an active photo session. Open the task, check the product requirements and select <strong>Start Photo Session</strong> when shooting begins.</p>
                <div class="manual-flow"><span>Open task</span><i class="fas fa-arrow-right"></i><span>Start session</span><i class="fas fa-arrow-right"></i><span>Upload photos</span><i class="fas fa-arrow-right"></i><span>Complete session</span></div>
                <p>Only complete the photo session after every required product photo is uploaded to the correct order.</p>
            </div>
        </section>

        <section id="statuses" class="manual-section">
            <span class="manual-section__number">05</span>
            <div>
                <h2>Order status guide</h2>
                <div class="table-responsive manual-table-wrap">
                    <table class="table manual-table">
                        <thead><tr><th>Status</th><th>What it means</th><th>Typical next action</th></tr></thead>
                        <tbody>
                            <tr><td>Pending</td><td>Order is created.</td><td>Assign or prepare it.</td></tr>
                            <tr><td>Assigned / In Progress</td><td>Design work is allocated or underway.</td><td>Designer uploads and submits design.</td></tr>
                            <tr><td>Pending Approval</td><td>Design is ready for owner review.</td><td>Approve or request revision.</td></tr>
                            <tr><td>Printing / Ready at HQ</td><td>Production is complete or ready for HQ.</td><td>Arrange photo session or fulfilment.</td></tr>
                            <tr><td>Photo Session / Photo Completed</td><td>Photography is underway or complete.</td><td>Upload images, then arrange delivery or pickup.</td></tr>
                            <tr><td>Out for Delivery / Waiting for Pickup</td><td>Order is with delivery or ready to collect.</td><td>Confirm delivered or picked up.</td></tr>
                            <tr><td>Completed</td><td>The customer has received the order.</td><td>No further workflow action.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="checks" class="manual-section manual-section--last">
            <span class="manual-section__number">06</span>
            <div>
                <h2>Final checks before moving an order</h2>
                <ul class="manual-checks">
                    <li><i class="fas fa-check"></i> Confirm the order number and customer name.</li>
                    <li><i class="fas fa-check"></i> Review the due date, brief and attachments.</li>
                    <li><i class="fas fa-check"></i> Make sure every uploaded design or photo belongs to the correct order.</li>
                    <li><i class="fas fa-check"></i> Update a status only when that stage is genuinely complete.</li>
                </ul>
                <p>If something is unclear, do not move the order forward. Review the order timeline and files first, then contact the relevant team member.</p>
            </div>
        </section>
    </div>
</div>
@endsection

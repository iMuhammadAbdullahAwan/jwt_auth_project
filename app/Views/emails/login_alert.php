<h2>Hello <?= esc($first_name) ?>,</h2>
<p>We noticed a login to your account:</p>
<ul>
    <li><b>Time:</b> <?= esc($time) ?></li>
    <li><b>IP Address:</b> <?= esc($ip) ?></li>
</ul>
<p>If this wasn’t you, please reset your password immediately.</p>
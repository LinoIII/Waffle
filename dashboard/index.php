<?php
// dashboard/index.php
require_once __DIR__ . '/../header.php';

/* Fallback anti-warning: se il backend non valorizza, uso defaults */
$totalEvents  = $totalEvents  ?? 0;
$userCount    = $userCount    ?? 0;
$recentEvents = is_array($recentEvents ?? null) ? $recentEvents : [];

/* Mock opzionale per vista se array vuoto */
if (!$recentEvents) {
  $recentEvents = [
    ['id'=>101, 'date'=>date('Y-m-d H:i'), 'severity'=>5, 'severityLabel'=>'High'],
    ['id'=>102, 'date'=>date('Y-m-d H:i', strtotime('-15 min')), 'severity'=>3, 'severityLabel'=>'Medium'],
    ['id'=>103, 'date'=>date('Y-m-d H:i', strtotime('-1 hour')), 'severity'=>1, 'severityLabel'=>'Low'],
  ];
  $totalEvents = max($totalEvents, 1234);
  $userCount   = max($userCount, 7);
}
?>

<section class="grid auto" aria-label="KPI">
  <div class="card">
    <h3 class="card__title">Totale eventi</h3>
    <div class="kpi"><?php echo number_format((int)$totalEvents); ?></div>
    <div class="kpi-sub muted">Ultime 24h</div>
  </div>
  <div class="card">
    <h3 class="card__title">Utenti</h3>
    <div class="kpi"><span class="badge badge--ok"><?php echo (int)$userCount; ?></span></div>
    <div class="kpi-sub muted">Attivi</div>
  </div>
  <div class="card">
    <h3 class="card__title">Criticità</h3>
    <?php
      $critical = array_reduce($recentEvents, fn($a,$e)=>$a+($e['severity']>=5?1:0), 0);
    ?>
    <div class="kpi"><span class="badge badge--err"><?php echo (int)$critical; ?></span></div>
    <div class="kpi-sub muted">Severità ≥ 5</div>
  </div>
  <div class="card">
    <h3 class="card__title">False Positive</h3>
    <div class="kpi"><span class="badge badge--ok"><?php echo (int)($false_positive ?? 0); ?></span></div>
  </div>
</section>

<section class="grid auto" aria-label="Charts" style="margin-top:16px">
  <div class="card">
    <h3 class="card__title">Trend eventi (7 giorni)</h3>
    <canvas id="chart-line" width="600" height="240" aria-label="Line chart"></canvas>
    <div class="card__meta muted">Mock client-side</div>
  </div>
  <div class="card">
    <h3 class="card__title">Top categorie</h3>
    <canvas id="chart-bar" width="600" height="240" aria-label="Bar chart"></canvas>
    <div class="card__meta muted">Mock client-side</div>
  </div>
  <div class="card">
    <h3 class="card__title">Severità</h3>
    <canvas id="chart-donut" width="600" height="240" aria-label="Donut chart"></canvas>
    <div class="card__meta muted">Mock client-side</div>
  </div>
</section>

<section class="card" aria-label="Eventi recenti" style="margin-top:16px">
  <h3 class="card__title">Eventi recenti</h3>
  <?php if ($recentEvents): ?>
  <table class="table">
    <thead>
      <tr><th>ID</th><th>Data</th><th>Gravità</th><th>Azioni</th></tr>
    </thead>
    <tbody>
      <?php foreach($recentEvents as $ev): ?>
        <tr>
          <td><?php echo htmlspecialchars((string)$ev['id']); ?></td>
          <td><?php echo htmlspecialchars((string)$ev['date']); ?></td>
          <td>
            <?php
              $sev = (int)$ev['severity'];
              $cls = $sev>=5 ? 'badge--err' : ($sev>=3 ? 'badge--warn' : 'badge--ok');
            ?>
            <span class="badge <?php echo $cls; ?>">
              <?php echo htmlspecialchars($ev['severityLabel'] ?? ( $sev>=5?'High':($sev>=3?'Medium':'Low') )); ?>
            </span>
          </td>
          <td><a class="btn btn--ghost" href="<?php echo htmlspecialchars('../filtershow.php?a_uniqid=' . urlencode($ev['id'])); ?>">Dettagli</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p class="muted">Nessun evento disponibile.</p>
  <?php endif; ?>
</section>

<script>
// Grafici mock in vanilla Canvas
(function(){
  function grid(ctx,w,h){ctx.save();ctx.strokeStyle='rgba(255,255,255,.08)';for(let x=0;x<=w;x+=w/6){ctx.beginPath();ctx.moveTo(x,0);ctx.lineTo(x,h);ctx.stroke()}for(let y=0;y<=h;y+=h/4){ctx.beginPath();ctx.moveTo(0,y);ctx.lineTo(w,y);ctx.stroke()}ctx.restore()}
  function line(id,d){const c=document.getElementById(id);if(!c)return;const x=c.getContext('2d'),w=c.width,h=c.height;grid(x,w,h);const M=Math.max(...d)*1.1,P=24,S=(w-2*P)/(d.length-1);x.beginPath();d.forEach((v,i)=>{const X=P+i*S,Y=h-P-(v/M)*(h-2*P);i?x.lineTo(X,Y):x.moveTo(X,Y)});x.strokeStyle='#4da3ff';x.lineWidth=2;x.stroke()}
  function bar(id,l,d){const c=document.getElementById(id);if(!c)return;const x=c.getContext('2d'),w=c.width,h=c.height;grid(x,w,h);const M=Math.max(...d)*1.1,P=24,B=(w-2*P)/d.length*.6;d.forEach((v,i)=>{const X=P+i*((w-2*P)/d.length)+((w-2*P)/d.length-B)/2,H=(v/M)*(h-2*P);x.fillStyle='#4da3ff';x.fillRect(X,h-P-H,B,H);x.fillStyle='rgba(230,238,246,.8)';x.font='12px system-ui';x.textAlign='center';x.fillText(l[i],X+B/2,h-6)})}
  function donut(id,s){const c=document.getElementById(id);if(!c)return;const x=c.getContext('2d'),w=c.width,h=c.height,Cx=w/2,Cy=h/2,R=Math.min(w,h)/3,T=R*.5,t=s.reduce((a,b)=>a+b.value,0)||1;let a=-Math.PI/2,colors=['#22c55e','#f59e0b','#ef4444'];x.clearRect(0,0,w,h);s.forEach((it,i)=>{const ang=(it.value/t)*Math.PI*2;x.beginPath();x.arc(Cx,Cy,R,a,a+ang);x.arc(Cx,Cy,R-T,a+ang,a,true);x.closePath();x.fillStyle=colors[i%colors.length];x.fill() ;a+=ang});x.fillStyle='rgba(230,238,246,.9)';x.font='600 18px system-ui';x.textAlign='center';x.textBaseline='middle';x.fillText(t+' evt',Cx,Cy)}
  line('chart-line',[12,18,14,22,19,24,31]);
  bar('chart-bar',['SQLi','XSS','LFI','RCE','Auth'],[34,21,15,8,13]);
  donut('chart-donut',[{label:'Low',value:8},{label:'Mid',value:5},{label:'High',value:3}]);
})();
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>

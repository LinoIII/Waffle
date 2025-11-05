...
<form method="GET" action="<?PHP print $thisPage; ?>" id="advFilterForm" class="kpi-card" style="margin:24px 0">
  <!-- Cambia le classi degli input e label, usa badge, btn, ecc. -->
  <fieldset>
    <legend class="badge badge-ok">&nbsp;General&nbsp;</legend>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
      <div>
        <label>Date From
          <input type="text" class="input" name="StDate" id="DateFrom" value="<?PHP echo $_SESSION['filter']['StDate']; ?>">
        </label>
      </div>
      <div>
        <label>Date To
          <input type="text" class="input" name="FnDate" id="DateTo" value="<?PHP echo $_SESSION['filter']['FnDate']; ?>">
      </div>
    </div>
    <!-- Il resto del form segue lo stesso pattern -->
  </fieldset>
  <button type="submit" class="btn btn-ok">Applica filtro</button>
</form>
...
<form id="frmModule">
<table id="tab-penalty">
<tr><td><input type="checkbox" id="impose" checked="checked" onchange="wiPenalty(this.id);" />&nbsp;<label for="impose">Impose</label></td><td><input type="checkbox" id="waive" onchange="wiPenalty(this.id);" />&nbsp;<label for="waive">Waive</label></td></tr>
<tr><td colspan="2">Reason(s) for waiving:</td></tr>
<tr><td colspan="2"><textarea id="reason" cols="25" rows="5" disabled></textarea></td></tr>
</table>
</form>
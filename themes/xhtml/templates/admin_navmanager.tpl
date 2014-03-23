{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="block-header2">Navigation for intern pages</div>
<form action ='{$kb_host}/?a=admin_navmanager' method='post'>
	<table class="kb-table">
		<tr>
			<th width='100'>
				<u>
					<b>Page</b>
				</u>
			</th>
			<th></th>
			<th colspan='2'>
				<u>Actions</u>
			</th>
		</tr>
		{foreach from=$inlinks item="i"}
			<tr class='kb-table-row-odd'>
				<td>{$i.name}</td>
				<td>
					<input type='text' name='name[{$i.id}]'value='{$i.name}' />
				</td>
				<td>
					<input type='submit' name='rename[{$i.id}]' value='rename' />
				</td>
				<td>
					<input type='submit' name='{if $i.hidden}show{else}hide{/if}[{$i.id}]' value='{if $i.hidden}show{else}hide{/if}' />
				</td>
			</tr>
		{/foreach}
	</table>

	<div class='block-header2'>Navigation for extern pages</div>
	<table class='kb-table'>
		<tr>
			<td width='100'>
				<u><b>Page</b></u>
			</td>
			<th colspan='2'>Rename</th>
			<th colspan='2'>URL</th>
			<th></th>
		</tr>
		<tr class='kb-table-row-odd'>
			<td colspan='6'>
				<b>
					<u>New Page:</u>
				</b>
			</td>
		</tr>
		{foreach from=$outlinks item="i"}
			<tr class='kb-table-row-odd'>
				<td>{$i.name}</td>
				<td>
					<input type='text' name='name[{$i.id}]' value='{$i.name}' />
				</td>
				<td>
					<input type='submit' name='rename[{$i.id}]' value='rename' />
				</td>
				<td>
					<input type='text' name='url[{$i.id}]' value='{$i.url}' />
				</td>
				<td>
					<input type='submit'  name='change[{$i.id}]' value='change' />
				</td>
				<td>
					<input type='submit' name='delete[{$i.id}]' value='delete' />
				</td>
			</tr>
		{/foreach}
		<tr class='kb-table-row-odd'>
			<td>Description:</td>
			<td>
				<input name='newname' id='newname' type='text' />
			</td>
			<td>URL:</td>
			<td colspan='2'>
				<input name='newurl' type='text' value='' />
			</td>
			<td>
				<input type='submit' name='add' value='add' />
			</td>
		</tr>
	</table>

	<div class='block-header2'>Order of the pages in Top Navigation Bar</div>
	<table class='kb-table'>
		<tr>
			<th>
				<u>Nr</u>
			</th>
			<td>
				<u>
					<b>Page</b>
				</u>
			</td>
			<th colspan='2'>
				<u>Actions</u>
			</th>
		</tr>
		{foreach from=$alllinks item='i'}
			<tr class='kb-table-row-odd'>
				<td align='right'>{$i.pos}</td>
				<td>{$i.name}</td>
				<td>
					<a href='{$kb_host}/?a=admin_navmanager&amp;decPrio={$i.id}'>
						<b> move up </b>
					</a>
				</td>
				<td>
					<a href='{$kb_host}/?a=admin_navmanager&amp;incPrio={$i.id}'>
						<b> down </b>
					</a>
				</td>
			</tr>
		{/foreach}
	</table>

	<div class='block-header2'>Reset Navigation Bar</div>
	<p>Return to the default navigation values</p>
	<input type='submit' name='reset' value='Reset' />

</form>

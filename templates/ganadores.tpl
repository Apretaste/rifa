<h1>Ganadores de la Rifa</h1>
<p>A continuaci&oacute;n los ganadores de las seis &uacute;ltimas rifas.</p>

{space15}

<table width="100%">
	{foreach from=$raffles item=$raffle name=winners}
		<tr>
			<td colspan="3" align="center">
				<h2>{$raffle->start_date|date_format:"%B del %Y"}</h2>
			</td>
		</tr>
		<tr>
			<td align="center" valign="top">
				<font color="#FFD700" size="8">&#10102;</font><br/>
				{if $raffle->winner_1_picture}
					{img src="{$raffle->winner_1_picture}" alt="{$raffle->winner_1_username}" width="100" height="100"}
				{else}
					{noimage width="100%" height="100" text="Aun sin<br/>foto :'-("}
				{/if}
				<p>{link href="PERFIL @{$raffle->winner_1_username}" caption="@{$raffle->winner_1_username}"}</p>
			</td>
			<td align="center" valign="top">
				<font color="#A9A9A9" size="8">&#10103;</font><br/>
				{if $raffle->winner_1_picture}
					{img src="{$raffle->winner_2_picture}" alt="{$raffle->winner_2_username}" width="100" height="100"}
				{else}
					{noimage width="100%" height="100" text="Aun sin<br/>foto :'-("}
				{/if}
				<p>{link href="PERFIL @{$raffle->winner_2_username}" caption="@{$raffle->winner_2_username}"}</p>
			</td>
			<td align="center" valign="top">
				<font color="#cd7f32" size="8">&#10104;</font><br/>
				{if $raffle->winner_1_picture}
					{img src="{$raffle->winner_3_picture}" alt="{$raffle->winner_3_username}" width="100" height="100"}
				{else}
					{noimage width="100%" height="100" text="Aun sin<br/>foto :'-("}
				{/if}
				<p>{link href="PERFIL @{$raffle->winner_3_username}" caption="@{$raffle->winner_3_username}"}</p>
			</td>
		</tr>
		{if not $smarty.foreach.winners.last}
			<tr>
				<td colspan="3">
					{space10}
					<hr/>
					{space10}
				</td>
			</tr>
		{/if}
	{/foreach}
</table>

{space30}

<center>
	{button href="RIFA" caption="Rifa en Curso"}
</center>

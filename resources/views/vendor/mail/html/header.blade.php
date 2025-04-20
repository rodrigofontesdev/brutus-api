@props(['url'])
<tr>
<td class="header">
<a href="{{ config('app.client.url') }}" style="display: inline-block;">
<img src="{{ config('app.url') }}/logo.svg" class="logo" alt="{{ $slot }}">
</a>
</td>
</tr>

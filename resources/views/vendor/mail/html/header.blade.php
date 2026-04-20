@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://book.thepride.id/images/mybook_logo.png" class="logo" alt="MyBook Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>

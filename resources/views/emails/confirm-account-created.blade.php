<x-mail::message>
# Confirm Account Created

Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorum quo deserunt blanditiis nesciunt labore, dolor illo alias tempora provident!

<x-mail::button :url="$link">
Confirm my account
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

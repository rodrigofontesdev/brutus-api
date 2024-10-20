<x-mail::message>
<x-mail::panel>
    **{{$secretWord}}**
</x-mail::panel>

# Access Your Account

Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorum quo deserunt blanditiis nesciunt labore, dolor illo alias tempora provident!

<x-mail::button :url="$link">
    Access my account
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
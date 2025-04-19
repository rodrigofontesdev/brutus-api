<x-mail::message>
@empty(!$secretWord)
<x-mail::panel>
**{{$secretWord}}**
</x-mail::panel>
@endempty

# Olá {{ $firstName }},

Lembre-se que o vencimento do seu DAS referente a **{{ $period }}** está se aproximando, o prazo é até o dia 20 do mês.

Mantenha seu MEI em dia, evite juros e multas.

<x-mail::button :url="$link">
Emitir DAS
</x-mail::button>

<x-mail::subcopy>
Atenciosamente,

**Equipe Brutus**<br>
_Simplificando a vida do MEI_
</x-mail::subcopy>
</x-mail::message>

<x-mail::message>
@empty(!$secretWord)
<x-mail::panel>
**{{$secretWord}}**
</x-mail::panel>
@endempty

# Olá {{ $firstName }},

Passando para te lembrar da importância de entregar a sua Declaração Anual de Faturamento (DASN-SIMEI) referente ao ano de **{{ $period }}**.

Não deixe para a última hora! O prazo final é até **31 de maio de {{ now()->year }}**.

<x-mail::button :url="$link">
Entregar DASN-SIMEI
</x-mail::button>

<x-mail::subcopy>
Atenciosamente,

**Equipe Brutus**<br>
_Simplificando a vida do MEI_
</x-mail::subcopy>
</x-mail::message>

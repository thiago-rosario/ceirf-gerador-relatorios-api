<table class="answers">
    <thead>
        <tr>
            <th class="question">Item verificado</th>
            <th class="answer">SIM</th>
            <th class="answer">NÃO</th>
            <th class="answer">NÃO SE APLICA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($answers as $question => $answer)
            <tr>
                <td>{{ $question }}</td>
                <td class="answer">{{ $answer === 'SIM' ? 'X' : '' }}</td>
                <td class="answer">{{ $answer === 'NÃO' ? 'X' : '' }}</td>
                <td class="answer">{{ $answer === 'NÃO SE APLICA' ? 'X' : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

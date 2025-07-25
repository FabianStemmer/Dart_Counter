@extends('layouts.app')

@section('content')
<div id="wrapper_div">

    {{-- Header --}}
    <div id="div_Titel">
        <img src="{{ asset('images/sos_logo.jpg') }}" alt="Sophiensaele Logo" style="height: 60px; vertical-align: middle; margin-right: 10px;">
        Dart Counter
    </div>

    {{-- Aktueller Spieler --}}
    <div id="div_Spieler">
        Wurf eingeben für: <span style="color:blue">{{ $game['players'][$game['current']]['name'] }}</span>
    </div>

    {{-- Hauptbereich: Zwei-Spalten-Layout --}}
    <div id="div_Parent_Hauptfenster">

        {{-- Linke Spalte: Punktebereich + Infos --}}
        <div id="div_Daten">

            {{-- Punkteübersicht --}}
            <div id="div_Punktebereich">

                {{-- Gewinnmeldung --}}
                @if ($game['winner'])
                    <div class="winner-headline">🎉 {{ $game['winner'] }} hat gewonnen! 🎉</div>
                    <form method="POST" action="{{ route('dart.newround') }}" style="margin-top: 1rem;">
                        @csrf
                        <button type="submit">Neue Runde mit den gleichen Spielern</button>
                    </form>
                @endif

                {{-- Punktetabelle --}}
                <h2 style="margin-top: 1rem;">
                    Punktestände Leg {{ $game['legNumber'] ?? 1 }}, Runde {{ $game['roundNumber'] ?? 1 }}
                </h2>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th style="text-align:right;">Win</th>
                            <th style="text-align:right;">Punkte</th>
                            <th style="text-align:right;">Darts</th>
                            <th style="text-align:right;">❌ Misses</th>
                            <th style="text-align:right;">Ø (3 Dart)</th>
                            <th style="text-align:right;">Ø (1 Dart)</th>
                        </tr>
                    </thead>
                    <tbody id="playerTableBody">
                        @foreach($game['players'] as $i => $player)
                            <tr class="player-row @if($i == $game['current'] && !$game['winner']) active @endif">
                                <td>{{ $player['name'] }}</td>
                                <td style="text-align:right;" id="legs-{{ $i }}">{{ $player['legs'] ?? 0 }}</td>
                                <td style="text-align:right;" id="score-{{ $i }}">{{ $player['score'] }}</td>
                                <td style="text-align:right;" id="darts-{{ $i }}">{{ $player['total_darts'] ?? 0 }}</td>
                                <td style="text-align:right;" id="misses-{{ $i }}">{{ $player['misses'] ?? 0 }}</td>
                                <td style="text-align:right;" id="average-{{ $i }}">{{ $player['average'] ?? 0 }}</td>
                                <td style="text-align:right;" id="average1dart-{{ $i }}">{{ $player['average_1dart'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Wurfanzeige --}}
            <div class="info-row">
                Aktuelle Würfe:
                <span>
                    <span id="wurf0display">–</span> /
                    <span id="wurf1display">–</span> /
                    <span id="wurf2display">–</span>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>Summe:</strong> <span id="roundsum">0</span>
                </span>
            </div>

            {{-- Checkout-Hilfe --}}
            <div class="info-row">
                Checkout-Hilfe:
                <span id="checkoutHilfe">–</span>
            </div>

            <div class="toggle-container" style="margin-top: 1em; margin-bottom:1em; display: flex; align-items: center; gap: 1.5em;">
                <label class="switch" style="margin: 0;">
                    <input type="checkbox" id="doubleInToggle" name="doubleInToggle" @if(!empty($game['doubleInRequired'])) checked @endif>
                    <span class="slider round"></span>
                </label>
                <span>Double In aktivieren</span>

                <label class="switch" style="margin: 0;">
                    <input type="checkbox" id="doubleOutToggle" name="doubleOutToggle" @if(!empty($game['doubleOutRequired'])) checked @endif>
                    <span class="slider round"></span>
                </label>
                <span>Double Out aktivieren</span>
            </div>

            {{-- Uhrzeit + Dauer --}}
            <div class="info-row zeitdauer">
                <div id="uhrzeit">Uhrzeit: 00:00:00</div>
                <div id="spieldauer">Dauer: 00:00</div>
            </div>
        </div>

        {{-- Spaltentrenner --}}
        <div id="div_Hauptfenster_Trennung"></div>

        {{-- Rechte Spalte: Dartboard und Eingabe --}}
        <div id="div_Eingabe">
            <form id="dart-form" method="POST" action="{{ route('dart.throw') }}" @if($game['winner']) style="display:none;" @endif>
                @csrf
                <input type="hidden" name="final_duration" id="final_duration" value="">

                @for ($i = 0; $i < 3; $i++)
                    <input type="hidden" name="throws[{{ $i }}][points]" id="points{{ $i }}" value="0">
                    <input type="hidden" name="throws[{{ $i }}][multiplier]" id="multiplier{{ $i }}" value="1">
                @endfor

                {{-- Dartboard --}}
                <div class="dart-board">
                    @for($i = 1; $i <= 20; $i++)
                        <button type="button" class="dart-btn" data-value="{{ $i }}">{{ $i }}</button>
                    @endfor
                    <button type="button" class="dart-btn" data-value="25">🎯</button>
                    <div></div>
                    <button type="button" class="dart-btn miss-btn" data-value="0">Miss</button>
                </div>

                {{-- Multiplier-Buttons + Zurück --}}
                <div style="margin-bottom: 1em;">
                    <button type="button" class="dart-btn multiplier-btn" data-mul="2">Double</button>
                    <button type="button" class="dart-btn multiplier-btn" data-mul="3">Triple</button>
                    <button type="button" class="dart-btn" id="reset-btn">Letzten Wurf zurück</button>
                </div>

                {{-- Weiter --}}
                <div style="display:flex; justify-content:end;">
                    <button type="button" id="next-btn" style="display:none;">Weiter</button>
                </div>
            </form>

            {{-- Rücksetzen --}}
            <form method="POST" action="{{ route('dart.reset') }}" style="margin-top: 1em;">
                @csrf
                <button type="submit">Spiel zurücksetzen</button>
            </form>
        </div>
    </div>

    {{-- Footer --}}
    <div id="div_footer">
        <div id="footer_left">Version 0.5 (Beta)</div>
        <div id="footer_center">© 2025 Stemmer Software Systems Engineering</div>
        <div id="footer_right">Build 1826.20250723</div>
    </div>

</div>
@endsection

@section('scripts')
{{-- Checkout table --}}
<script>
window.checkoutTable = @json(include(app_path('CheckoutTable.php')));
</script>

{{-- Hauptlogik --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const winner = @json($game['winner'] ? true : false);
    const finalDuration = @json($game['final_duration'] ?? null);
    const startTime = new Date("{{ \Carbon\Carbon::parse($game['start_time'])->toIso8601String() }}");

    let initialScore = {{ $game['players'][$game['current']]['score'] }};
    let currentPlayer = {{ $game['current'] }};
    let currentThrow = 0;
    let throwData = [{points:0,multiplier:1},{points:0,multiplier:1},{points:0,multiplier:1}];
    let multiplier = 1;

    function updateDisplay() {
        let sum = 0;
        for(let i = 0; i < 3; i++) {
            let val = throwData[i].points * throwData[i].multiplier;
            document.getElementById('wurf' + i + 'display').textContent =
                (currentThrow > i || throwData[i].points > 0) ?
                (throwData[i].points + (throwData[i].multiplier > 1 ? 'x'+throwData[i].multiplier : '')) : '–';
            sum += val;
            document.getElementById('points' + i).value = throwData[i].points;
            document.getElementById('multiplier' + i).value = throwData[i].multiplier;
        }

        document.getElementById('roundsum').textContent = sum;
        const newScore = initialScore - sum;
        document.getElementById('score-' + currentPlayer).textContent = newScore;

        const tip = window.checkoutTable?.[newScore];
        document.getElementById('checkoutHilfe').textContent = tip ? tip.join(' – ') : '–';

        let message = '';
        if (newScore < 2 && newScore !== 0) message = "🚫 Bust! Bitte prüfen und Weiter klicken.";
        if (newScore === 0) message = "🎉 Gewonnen! Bitte prüfen und Weiter klicken.";

        let resultHint = document.getElementById('result-hint');
        if (!resultHint && message) {
            resultHint = document.createElement('div');
            resultHint.id = 'result-hint';
            resultHint.classList.add('bust-message');
            document.querySelector('#div_Punktebereich').appendChild(resultHint);
        }
        if (resultHint) resultHint.innerHTML = message;

        // Hole aktuellen Spieler vom Server-Game-Array, falls vorhanden
        const players = @json($game['players']);
        const player = players[currentPlayer];

        const dartsThisRound = throwData.slice(0, currentThrow).length;
        const sumMisses = throwData.slice(0, currentThrow).filter(t => t.points === 0).length;

        const totalDarts = (player.total_darts || 0) + dartsThisRound;
        const totalPoints = (player.total_points || 0) + sum;
        const totalMisses = (player.misses || 0) + sumMisses;
        const average3dart = totalDarts > 0 ? (totalPoints / totalDarts * 3) : 0;
        const average1dart = totalDarts > 0 ? (totalPoints / totalDarts) : 0;

        document.getElementById('darts-' + currentPlayer).textContent = totalDarts;
        document.getElementById('average-' + currentPlayer).textContent = average3dart.toFixed(1);
        document.getElementById('average1dart-' + currentPlayer).textContent = average1dart.toFixed(1);
        document.getElementById('misses-' + currentPlayer).textContent = totalMisses;
        document.getElementById('legs-' + currentPlayer).textContent = player.legs ?? 0;

        document.getElementById('next-btn').style.display =
            (!winner && ((newScore === 0 || (newScore < 2 && newScore !== 0)) || currentThrow === 3))
            ? 'inline-block' : 'none';
    }

    document.querySelectorAll('.dart-btn[data-value]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentThrow < 3) {
                btn.classList.add('spin');
                setTimeout(() => btn.classList.remove('spin'), 600);
                throwData[currentThrow] = {
                    points: parseInt(btn.dataset.value),
                    multiplier: multiplier
                };
                multiplier = 1;
                document.querySelectorAll('.multiplier-btn').forEach(b => b.classList.remove('selected'));
                currentThrow++;
                updateDisplay();
            }
        });
    });

    document.querySelectorAll('.multiplier-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            multiplier = parseInt(btn.dataset.mul);
            document.querySelectorAll('.multiplier-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        });
    });

    document.getElementById('reset-btn').onclick = () => {
        if (currentThrow > 0) {
            currentThrow--;
            throwData[currentThrow] = { points: 0, multiplier: 1 };
            document.getElementById('next-btn').style.display = 'none';
            updateDisplay();
        }
    };

    document.getElementById('next-btn').onclick = () => {
        const resultHint = document.getElementById('result-hint');
        if (resultHint) {
            resultHint.remove();
        }
        document.getElementById('final_duration').value =
        document.getElementById('spieldauer').textContent.replace('Dauer: ', '');
        document.getElementById('dart-form').submit();
    };

    const form = document.getElementById('dart-form');

    const doubleInToggle = document.getElementById('doubleInToggle');
    if (doubleInToggle && form) {
        let hiddenDoubleIn = document.createElement('input');
        hiddenDoubleIn.type = 'hidden';
        hiddenDoubleIn.name = 'doubleInRequired';
        hiddenDoubleIn.value = doubleInToggle.checked ? '1' : '0';
        form.appendChild(hiddenDoubleIn);

        doubleInToggle.addEventListener('change', () => {
            hiddenDoubleIn.value = doubleInToggle.checked ? '1' : '0';
        });
    }

    const doubleOutToggle = document.getElementById('doubleOutToggle');
    if (doubleOutToggle && form) {
        let hiddenDoubleOut = document.createElement('input');
        hiddenDoubleOut.type = 'hidden';
        hiddenDoubleOut.name = 'doubleOutRequired';
        hiddenDoubleOut.value = doubleOutToggle.checked ? '1' : '0';
        form.appendChild(hiddenDoubleOut);

        doubleOutToggle.addEventListener('change', () => {
            hiddenDoubleOut.value = doubleOutToggle.checked ? '1' : '0';
        });
    }

    function moveActivePlayerToTop() {
    const tbody = document.getElementById('playerTableBody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const active = rows.find(row => row.classList.contains('active'));
    if (active && tbody.firstChild !== active) {
        active.classList.add('moveup');
        tbody.insertBefore(active, tbody.firstChild);
        setTimeout(() => active.classList.remove('moveup'), 500);
    }
    }    

    // Uhrzeit & Daueranzeige aktualisieren
    setInterval(() => {
        const now = new Date();
        const uhr = now.toLocaleTimeString('de-DE');
        document.getElementById('uhrzeit').textContent = "Uhrzeit: " + uhr;

        if (winner && finalDuration) {
            document.getElementById('spieldauer').textContent = 'Dauer: ' + finalDuration;
            return;
        }

        const ms = now - startTime;
        const min = Math.floor(ms / 60000);
        const sec = Math.floor((ms % 60000) / 1000);
        document.getElementById('spieldauer').textContent = `Dauer: ${String(min).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
    }, 1000);

    updateDisplay();
});
</script>
@endsection
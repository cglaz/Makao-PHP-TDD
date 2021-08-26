# Test Driven Development w PHP

## Project

Gra Karciana - MAKAO

## Specyfikacja projektu

1. Potrzebujemy do gry stołu.
2. Ilość graczy to od 2 do 4.
2.1. Nie możemy rozpocząć gry, jeżeli mamy mniej niż 2 graczy.
2.2. Nie możemy mieć więcej niż 4 graczy przy jednym stole.

3. Mamy do dyspozycji jedną talię kart.
3.1. Każda karta ma swoją wartość oraz kolor.
3.1.1. Dozwolone wartości to od 2 do 10 oraz Walet, Dama, Król i AS.
3.1.2. Dozwolone kolory to Karo, Pik, Trefl oraz Kier.
3.2. Każda karta w tali jest unikalna.
3.3. Mamy możliwość potasować naszą talię.

4. Mamy zasady, które sterują grą.
4.1. Na stole mamy potasowaną talie kart.
4.2. Każdy gracz dostaje 5 losowych kart z tali.
4.3. Na stole wykładam pierwszą kartę z tali i to będzie karta, która rozpoczyna grę.
4.4. W jednej turze gracz może zagrać tylko jedną kartą.
4.5. Jeżeli gracz nie ma odpowiedniej karty, to dobiera kartę z tali.
4.5.1. Gracz, który dobrał kartę, o ile może nią zagrać, może to zrobić w tej samej turze.
4.6. Jeżeli nie ma tyle kart do dobrania w tali, to zostawiamy na stole ostatnią kartę zagraną na stosie, a resztę tasujemy.
4.7. Gracz w swojej turze może zagrać kartą, która jest w tym samym kolorze lub o tej samej wartości.  
4.8. Kiedy graczowi zostanie ostatnia karta w ręku, musi powiedzieć "MAKAO".
4.8.1. Ponieważ jeżeli gracz nie powie "MAKAO" przed końcem swojej tury, to dobiera 5 kart z talii.
4.9. Wygrywa ten gracz, który jako pierwszy pozbędzie się wszystki swoich kart.

5. Karty funkcyjne
5.1. Karta 2 - kolejny gracz dobiera 2 karty z talii.  
5.2. Karta 3 - kolejny gracz dobiera 3 karty z talii.
5.3. Karta 4 - kolejny gracz traci swoją turę.
5.4. Walet - umożliwia żądanie dowolnej karty nie funkcyjnej od wszystkich innych graczy.
5.4.1. Żądanie kończy się w momencie, kiedy gracz, który je rozpoczął, położy żądane karty.
5.4.2. Podczas żądania gracz może zagrać więcej niż jedną kartą, o ile są to karty żądane.
5.4.3. Kolejny gracz może zmienić żądanie używając innego waleta.
5.5. Dama - gracz może zagrać damą na dowolną niefunkcjonalną kartę w dowolnym kolorze.
5.5.1. Na damę kolejny gracz może położyć dowolną kartę w dowolnym kolrze.
5.6. Król Kier - kolejny gracz dobiera 5 kart z talii.
5.6.1. Król Pik - poprzedni gracz dobiera 5 kart z talii.
5.6.2. Gracz, który zagrał Króla Pik, zyskuje dodatkową turę.
5.7. AS - zmienia wymagany kolor karty do zagrania.
5.8. Kolejny gracz może się obronić lub zmienić wymagania rzucając taką samą kartę funkcyjną w innym kolorze.
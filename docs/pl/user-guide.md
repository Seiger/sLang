# Przewodnik użytkownika

Ten przewodnik opisuje codzienną pracę z sLang: słownik, konfigurację języków i wielojęzyczne zakładki zasobu.

## Otwórz Moduł

W menu **Moduły** otwórz **Multilanguage**. **Dictionary** zarządza kluczami tłumaczeń. **Configuration** zarządza językiem domyślnym, językami strony, językami frontendu, segmentami URL i wielojęzycznymi TV.

## Utrzymuj Słownik

**Synchronize** skanuje szablony i pliki Blade, a następnie dodaje brakujące klucze do bazy. Akcja używa istniejącego parsera backendowego i nie powinna przeładowywać iframe managera.

Nowe klucze dodaje się zielonym przyciskiem. sLang otwiera modal tworzenia, w którym podajesz klucz oraz wartości dla aktualnie skonfigurowanych języków strony. Kolumna klucza pozostaje tylko do odczytu, aby lookup w szablonach był stabilny.

Przyciski tłumaczenia w wierszu uzupełniają jedną wartość. Przycisk w nagłówku kolumny uzupełnia wszystkie puste wartości tego języka.

## Skonfiguruj Języki

Najpierw wybierz język domyślny. **Use in URL** określa, czy język domyślny pojawia się w adresie.

Języki frontendu zależą od języków strony. `s_lang_front` musi być podzbiorem `s_lang_config`.

## Edytuj Zasoby

sLang dodaje zakładki językowe do edytora zasobu Evolution. Zapis odbywa się przez standardowe przyciski Evolution.

## Zalecana Kolejność

Dla nowej wielojęzycznej strony najpierw skonfiguruj języki:

1. Wybierz język domyślny.
2. Dodaj języki strony.
3. Aktywuj języki frontendu z tej listy.
4. Sprawdź segmenty URL.
5. Wybierz wielojęzyczne Template Variables.
6. Zapisz Configuration.
7. Uruchom synchronizację Dictionary.
8. Uzupełnij brakujące tłumaczenia ręcznie albo akcją bulk dla kolumny.

## Sprawdź Po Zmianach

Otwórz zasób i sprawdź, czy pola ogólne, TVs i ustawienia pokazują ten sam zestaw języków. Następnie sprawdź publiczne URL-e i przełącznik języka dla każdego języka frontendu.

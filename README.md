***CONTINUALLY GROWING HOMEWORK EXCERCISE - GRADUALLY ADDING FUNCTIONALITIES AND CONNECTING FRONT- AND BACKEND***

***Specifications for genesis of project:***

**+++BACKEND - STEP 1+++**
Planen und Implementieren Sie eine Web-Anwendung, die aus einer Datenbank Produkteausliest und nach Kategorien ausgibt. 
Die Anwendung bietet die Möglichkeit Listen vonProduktkategorien und Produkten auszugeben. Als Grundlage dient eine vorhandeneDatenbank (Diese finden Sie auf Moodle).
In der DB finden Sie unter anderen zwei Tabellen, welche die Produkte (products) und die Produktkategorien (product_types) enthalten, die über einen Fremdschlüssel (von
products.id_product_types nach product_types.id) verbunden sind.

Die Queries die Sie benötigen sind die beiden, wobei die php Variable natürlich einengültigen Wert enthalten muss (die ID zur gewählten Kategorie):

für die Liste der Kategorien
  SELECT id, name FROM product_types ORDER BY name
  
für die Produkte einer Kategorie
  SELECT t.name AS productTypeName, p.name AS prodcutName
  FROM product_types t
  JOIN products p ON t.id = p.id_product_types
  WHERE t.id = {$productTypeId};
  
**Schnittstelle**
Folgende zwei Parameter sollen über die Schnittstelle verarbeitet werden:
* GET Parameter String: resource (Kann die Werte types und products enthalten)
* GET Parameter Integer: filter-type (Im Fall products, enthält dieser Parameter die ID der gewählten Kategorie)
* 
2 Beispiele dazu:
http://localhost/Uebung3/index.php?resource=types
http://localhost/Uebung3/index.php?resource=products&filter-type=2

**+++FRONTEND - STEP 1+++**
Wir versuchen die LV mit Backenbasics langsam zuverbinden.
1.
Entwerfen Sie ein GUI, mit dem Sie die Produktliste, die in der DB gespeichert sind, strukturiert browsen können, wobei Page-Reloads vermieden werden sollen (AJAX ist gefordert).
Welche HTML Elemente benötigen Sie, welche JavaScript Objekt?
Denken Sie nun voraus: Die Produkte sollen später einer Bestellung hinzugefügtwerden, wie bei einem Webshop.
2.
Implementieren Sie die Bootstrap GUI. Binden Sie die GUI an Ihre Übung ausBackendbasics an und laden Sie die Produktkategorien.
Klickt man auf eine Kategorie, sollen die Produkte in übersichtlicher Formausgegeben werden. Welches Problem müssen Sie lösen um BE und FE zuverbinden?
Die Schnittstellen finden Sie in der Übung aus BEB.

**+++BACKEND - STEP 2+++**
**Planen und Implementieren:**
Erweitern Sie die Übung mit den Produktliste (gerne Ihren eigenen Code, oder dieMusterlösung) mit der Funktionalität eines Warenkorbs. 
Der Client soll über zwei Schnittstellen jeweils einen Artikel hinzufügen, oder entfernen können (mehrfach möglich: n mal hinzu oder weg => die Anzahl wird je Eintrag mehr oder weniger) 
und mit einerweiteren Schnittstelle die Liste der Waren im Warenkorb anzeigen können. Verwenden Sie weiterhin die MVC Architektur und überlegen Sie welche Technologien zum Behalten des Warenkorbinhalts eingesetzt werden kann.

**Schnittstelle**
* POST http://localhost/Uebung4/index.php?resource=cart&articleId=10
* DELETE http://localhost/Uebung4/index.php?resource=cart&articleId=10
* GET http://localhost/Uebung4/index.php?resource=cart
* Ergebnis nach Add oder Remove: {"state": "OK / ERROR"}
* Ergebnis der Cartliste:
{"cart":
  [
    {"articleName": "String", "amount": "Number"},
    /** ... **/
  ]
}

**+++FRONTEND - STEP 2+++**
1.
Erweitern Sie die Produktliste mit der Funktionalität, dass man einzelne Artikel einemWarenkorb hinzufügen kann.
War die Aktion erfolgreich, soll dem Benutzer dies mitgeteilt werden (Verwenden Sieein Bootstrap Modal dazu).
2.
Erstellen Sie eine zweite View in der der Benutzer den Warenkorb ansehen kann.Beide Views sollen über die Navbar aufgerufen werden können.
3.
In dieser Liste sollen die Artikel und die Stückzahl, so wie der Gesamtpreis proPosten und auch die Gesamtsumme angezeigt werden. Die Liste soll dynamischbeim Laden der View erzeugt werden.
4.
In dieser Liste soll es außerdem möglich sein, die Stückzahlen der Artikel weiter zuerhöhen oder eben zu verringern, bis der Artikel 0 wird. In dem Fall soll der Artikelnicht mehr in der Liste angezeigt werden.
Die Schnittstellendefinition finden Sie in der Übung aus BEB.

**+++BACKEND - STEP 3+++**
**Planen und Implementieren:**
Erweitern Sie das bestehende Projekt um 2 weitere Funktionalitäten.
1.
Der Benutzer soll die möglichkeit haben sich am System anzumelden. Erstellen Sie dazu Testuseraccounts und implementieren Sie eine Login- und Logout-variante.
Das System soll den aktuellen Benutzer wieder erkennen können (Session,Datenbank, Token?)
2.
Es soll die Möglichkeit (für angemeldete Benutzer) geben, den Warenkorb auf derDatenbank abspeichern zu können.
Und es soll die Möglichkeit die List der vergangen "Bestellungen" aufzulisten (Datum,der Bestellungen reicht aus)

**Schnittstelle**
* POST http://localhost/Uebung4/index.php?action=login (Username und Password alsParameter)
* POST http://localhost/Uebung4/index.php?action=logout
* POST http://localhost/Uebung4/index.php?resource=orders (Token, Cart alsParameter)
* GET http://localhost/Uebung4/index.php?resource=orders
* Ergebnis nach Login und Logout: {"state": "OK / ERROR"}
* Ergebnis der History: Überlegen Sie eine sinnvolle Repräsentation der Daten

**+++FRONTEND - STEP 3+++**
1.
Konzipieren und implementieren Sie ein Login Formular und die Übersicht über vergangene Bestellungen eines Benutzers (Datum und Gesamtbetrag reichen in derAnzeige aus)
2.
Verbinden Sie die beiden neuen GUI Elemente mit den vorhandenen API Schnittstellen. Für eingeloggte Benutzer erhalten Sie einen Token, der beim Aufruf der Bestellhistory mit übertragen werden muss.
3.
Fehlerhafte Logins ergeben eine entsprechende Fehlermeldung.
4.
Aufrufe der History ohne Token, oder falschen Token führen auch zu einer entsprechenden Fehlermeldung.
Die Schnittstellendefinition finden Sie in der Übung aus BEB.

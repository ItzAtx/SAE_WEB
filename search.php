<html>
    <link rel="stylesheet" href="css/search.css">
    <div class="header">
        <div class="profil">
            <a href="profil.php"><img src="images/user.png" class="user"></a>
        </div>

        <div class="search">
            <input class="searchbar" name="barreRecherche" placeholder="Rechercher">
            <button class="searchbutton">chercher</button>
        </div>
    </div>

    <div id="filtres">
        <div>
            <h3>Type</h3>

            <div>
                <input type="radio" name="boutonBoutique"> <label>Boutiques</label>
            </div>
            <div>
            <input type="radio" name="boutonEnclos"> <label>Enclos</label>
            </div>
        </div>

        <div>
            <h3>Zone</h3>
            <div><input type="checkbox" name="toutZone"> <label>Toutes</label></div>
            <div><input type="checkbox" name="z1"> <label>zone 1</label></div>
            <div><input type="checkbox" name="z2"> <label>zone 2</label></div>
            <div><input type="checkbox" name="z3"> <label>zone 3</label></div>
            <div><input type="checkbox" name="z4"> <label>zone 4</label></div>
        </div>

        <div>
            <h3>Type d'animal</h3>
            <div><input type="checkbox" name="toutAnimal"> <label>Tous</label></div>
            <div><input type="checkbox" name="z1"> <label>Mammifere</label></div>
            <div><input type="checkbox" name="z2"> <label>Vivipare</label></div>
            <div><input type="checkbox" name="z3"> <label>piafs</label></div>
            <div><input type="checkbox" name="z4"> <label>jsp frere</label></div>
        </div>
        


    </div>
</html>
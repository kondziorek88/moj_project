var computed = false;
var decimal = 0;

function convert (entryform, from, to)
{
    convertform = from.selectedIndex;
    convertto = to.selectedIndex
    entryform.display.value = (entryform.input.value * from[convertform].value / to[convertto].value);
}

function addChar (input, character)
{
    if((character=='.' && decimal=="0")||character!='.')
    {
        (input.value ==""|| input.value=="0")?input.value = character : input.value += character
        convert(input.form,input.form.measure1,input.form.measure2)
        computed= true;
        if(character=='.')
        {
            decimal =1;
        }
    }
}
function openVothcom()
{
    window.open("", "Display windom", "toolbar=no,directories=no,menubar=no");

}

function clear (form)
{
    form.input.value = 0;
    form.display.value = 0;
    decimal=0;
}
function changeBackground(hexnumber)
{
    document.bgColor = hexnumber
}
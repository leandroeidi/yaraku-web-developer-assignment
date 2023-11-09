function toggleFilter()
{
    filter = document.getElementById("filter");
    filter_content = document.getElementById("filter-content");
    if (filter.clientHeight == "0")
    {
        filter.style.height = filter_content.clientHeight + "px";
    }
    else
    {
        filter.style.height = 0;
    }
}

function resetFilterBooks()
{
    document.getElementById('filter-title').value = '';
    document.getElementById('filter-author').value = '';
}

function closeMessage()
{
    document.getElementById("message").style.display = "none";
}

function changeOrderBooks(field, order_field, order_direction)
{
    if (field != order_field)
    {
        order_direction = 'asc';
    }
    else
    {
        order_direction = order_direction == 'asc' ? 'desc' : 'asc';
    }

    document.getElementById('order_field').value = field;
    document.getElementById('order_direction').value = order_direction;
    document.getElementById('order_form').submit();
}
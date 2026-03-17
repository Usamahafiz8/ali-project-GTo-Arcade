// $(document).ready(function() {
//     const fpPromise = FingerprintJS.load()
//     fpPromise
//         .then(fp => fp.get())
//         .then(result => {
//             const visitorId = result.visitorId
//             $('form').each(function(index, $form) {
//                 $('<input>').attr({
//                     type: 'hidden',
//                     id: 'visitorId',
//                     name: 'visitorId',
//                     value: visitorId
//                 }).appendTo($form)
//             });
//         })
// })
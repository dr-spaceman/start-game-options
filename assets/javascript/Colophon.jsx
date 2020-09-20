import React from 'react';

import storageAvailable from './storageAvailable.js';

export default function Colophon() {
    if (!storageAvailable('localStorage')) {
        return '';
    }
    localStorage.clear();

    const [open, setOpen] = React.useState(true);

    function handleClose(event) {
        event.preventDefault();
        setOpen(false);
        localStorage.setItem('colophon', 'closed');
    }

    if (open && localStorage.getItem('colophon') !== 'closed') {
        return (
            <div className="container" style={{ position: 'fixed', zIndex:999, right:0, bottom:0, left:0, backgroundColor:'black', fontSize:'15px', color:'#BBB', boxShadow:'0 0 10px -5px black' }}>
                Welcome to Videogam.in, a site about videogames. <b><a href="/about.php">Read more</a></b> about this site or else <a
                    href="#close" title="hide this message and don't show it to me again" className="tooltip" onClick={handleClose}>pay me for the door repair charge</a>.
            </div>
        );
    }

    return '';
}

import React from 'react';

import storageAvailable from './storageAvailable.js';
import welcome from '../images/colophon_welcome.png';

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
            <div className="container dark" style={{ position: 'fixed', zIndex: 999, right: 0, bottom: 0, left: 0, fontSize: '15px', color: '#BBB', boxShadow: '0 0 10px -5px black' }}>
                <div style={{ width:'960px', padding:'30px 0', margin:'0 auto', textAlign:'center' }}>
                    Welcome to Videogam.in, a site about videogames.
                    <br />
                    <a href="/about.php"><strong>Read more</strong></a> about this site or else <a href="#close" title="hide this message and don't show it to me again" className="tooltip" onClick={handleClose}><strong>pay me for the door repair charge</strong></a>.
                </div>
                <div style={{ position: 'absolute', zIndex: 2, top:'10px', left: '50%', width:'192px', height:'16px', margin: '0 0 0 -96px', background: `url(${welcome}) no-repeat 0 0` }} />
                <div style={{ position: 'absolute', zIndex: 2, bottom:'0', left: '50%', width:'192px', height:'18px', margin: '0 0 0 -96px', background: `url(${welcome}) no-repeat 0 -16px` }} />
                <div style={{ position: 'absolute', zIndex: 1, right:'0', bottom: '0', left:'0', width:'100%', height: '18px', background: `url(${welcome}) repeat-x 0 -34px` }} />
            </div>
        );
    }

    return '';
}

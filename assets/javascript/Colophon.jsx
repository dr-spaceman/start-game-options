import React from 'react';

export default function Colophon() {
    const [open, setOpen] = React.useState(true);

    if (open) {
        return (
            <div>
                Welcome to Videogam.in, a site about videogames. <b><a href="/about.php">Read more</a></b> about this site or else <a
                    href="#close" title="hide this message and don't show it to me again" className="tooltip" onClick={() => setOpen(false)}>pay me for the door repair charge</a>.
            </div>
        );
    }

    return '';
}

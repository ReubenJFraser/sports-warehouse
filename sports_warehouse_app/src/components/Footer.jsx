import React from 'react';
import { Box, Tooltip, Typography } from '@mui/material';

const Footer = () => {
  return (
    <Box
      component="footer"
      sx={{ textAlign: 'center', p: 2, mt: 4, backgroundColor: 'background.paper' }}
    >
      <Typography variant="body2" component="p">
        Video 1:{" "}
        <Tooltip
          title="Coming Soon Neon (AdobeStock_316253953.mov, by Es Sarawuth): A COMING SOON neon sign flickers and crackles on a dark background, creating an exciting, anticipatory effect."
          arrow
        >
          <span style={{ textDecoration: 'underline', cursor: 'help' }}>
            Coming Soon Neon
          </span>
        </Tooltip>
      </Typography>
      <Typography variant="body2" component="p" sx={{ mt: 1 }}>
        Video 2:{" "}
        <Tooltip
          title="Quick High-Five in the Gym (AdobeStock_439110123.mov, by Kawee): A male and a female in a gym exchange a high-five; after the high-five, they cross their arms and look directly at the camera, conveying energy and camaraderie."
          arrow
        >
          <span style={{ textDecoration: 'underline', cursor: 'help' }}>
            Quick High-Five in the Gym
          </span>
        </Tooltip>
      </Typography>
      <Box sx={{ mt: 2 }}>
        <Typography variant="caption" display="block">
          Contact: 123-456-7890 | personal@example.com | student@university.edu
        </Typography>
      </Box>
    </Box>
  );
};

export default Footer;



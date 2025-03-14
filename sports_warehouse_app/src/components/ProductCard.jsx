// src/components/ProductCard.jsx
import React from 'react';
import { Card, CardContent, CardActions, Typography, Button } from '@mui/material';
import { usePaneContext } from '../contexts/PaneContext';

const ProductCard = ({ product }) => {
  const { setPaneContent, setSelectedProduct, setDrawerOpen } = usePaneContext();

  const handleViewDetails = () => {
    // Set the context to show product details in the sidebar
    setSelectedProduct(product);
    setPaneContent('details');
    setDrawerOpen(true);  // open the drawer if it's on mobile view
  };

  return (
    <Card variant="outlined">
      <CardContent>
        <Typography variant="h6">{product.name}</Typography>
        <Typography color="text.secondary">{product.price}</Typography>
        <Typography variant="body2" sx={{ mt: 1 }}>
          {product.description}
        </Typography>
      </CardContent>
      <CardActions>
        <Button size="small" onClick={handleViewDetails}>
          View Details
        </Button>
      </CardActions>
    </Card>
  );
};

export default ProductCard;
